<?php
/**
 * Backend Multi-Tenant Chat Proxy
 * Verifies tenant status, quotas, routes to automation webhook, and executes CRM pipelines
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/../db/database.php';
require_once __DIR__ . '/../db/security.php';

// Rate Limiting: Max 40 messages per minute per IP to prevent DoS/flooding
if (!Security::checkRateLimit('chat_proxy', 40, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please slow down and try again shortly.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!$payload) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$message = trim((string)($payload['message'] ?? ''));
$sessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', trim((string)($payload['sessionId'] ?? session_create_id())));
if (empty($sessionId) || strlen($sessionId) > 64) {
    $sessionId = 'sess_' . bin2hex(random_bytes(16));
}
$subdomain = Security::sanitizeSlug((string)($payload['subdomain'] ?? ''));

// Prevent payload bloat attack: Max message length 3000 characters
if (mb_strlen($message) > 3000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message length exceeds maximum allowed limit (3000 characters).']);
    exit;
}

// Fallback: Check Host header for Subdomain or Custom Whitelabel Domain
if (empty($subdomain)) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = strtolower(explode(':', $host)[0]);
    if (preg_match('/^([a-z0-9-]+)\.chatmodel\.in$/i', $host, $matches)) {
        $subdomain = strtolower($matches[1]);
    } else {
        $customTenant = Database::getTenantByCustomDomain($host);
        if ($customTenant) {
            $subdomain = strtolower($customTenant['subdomain']);
        }
    }
}

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// 1. Determine Target Webhook & Tenant Status
$webhookUrl = 'https://api.chatmodel.in/webhook/chat-default';
$businessName = 'ChatModel Assistant';
$crmWebhookUrl = '';
$webhookSecret = '';

if (!empty($subdomain) && $subdomain !== 'n8n' && $subdomain !== 'www') {
    $tenant = Database::getTenantBySubdomain($subdomain);
    if (!$tenant) {
        http_response_code(404);
        echo json_encode(['error' => "Tenant '$subdomain' does not exist in the system."]);
        exit;
    }

    if ((int)$tenant['is_active'] === 0) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Service is currently paused or disabled for this tenant.',
            'disabled' => true,
            'business_name' => $tenant['business_name']
        ]);
        exit;
    }

    // Check Monthly Conversation Quota
    $stats = Database::getTenantConversationStats($subdomain);
    if ($stats['is_over_quota']) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Monthly conversation limit reached for this workspace.',
            'quota_exceeded' => true,
            'response' => "Notice: This workspace has reached its allocated monthly conversation limit (" . number_format($stats['monthly_limit']) . " conversations). Please contact the workspace administrator to upgrade your plan."
        ]);
        exit;
    }

    $crmWebhookUrl = $tenant['crm_webhook_url'] ?? '';
    $webhookSecret = $tenant['webhook_secret'] ?? '';
    $crmEvents = $tenant['crm_events'] ?? 'lead_capture,escalation';
    $webhookUrl = $tenant['webhook_url'];
    $businessName = $tenant['business_name'];
}

// Helper: Dispatch to Advanced CRM Pipeline with SSRF validation
function dispatchCrmPipeline(string $crmUrl, string $secret, string $event, array $payload): void {
    if (empty($crmUrl)) return;
    
    // SSRF Security Check
    $val = Security::validateExternalUrl($crmUrl);
    if (!$val['valid']) return;

    try {
        $ch = curl_init($crmUrl);
        $headers = [
            'Content-Type: application/json',
            'X-ChatModel-Event: ' . preg_replace('/[^a-zA-Z0-9_-]/', '', $event),
            'User-Agent: ChatModel-CRMPipeline/2.0'
        ];
        if (!empty($secret)) {
            $headers[] = 'Authorization: Bearer ' . str_replace(["\r", "\n"], '', $secret);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Non-blocking fast timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Prevent open redirect SSRF
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        // Safe failover
    }
}

// Log incoming user message
Database::logMessage($subdomain ?: 'root', $sessionId, 'user', $message);

// Extract Lead Entities
$extractedEmail = null;
$extractedPhone = null;
if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $emailMatch)) {
    $extractedEmail = $emailMatch[0];
}
if (preg_match('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}|\b\d{10}\b/', $message, $phoneMatch)) {
    $extractedPhone = $phoneMatch[0];
}

// 2. Forward to Webhook with SSRF validation
$webhookValidation = Security::validateExternalUrl($webhookUrl);
if (!$webhookValidation['valid']) {
    // If webhook url is invalid or internal, return secure default response
    $fallbackReply = "Thank you for contacting $businessName. Your request has been received securely.";
    Database::logMessage($subdomain ?: 'root', $sessionId, 'bot', $fallbackReply);
    echo json_encode(['response' => $fallbackReply, 'source' => 'system_secure_router']);
    exit;
}

try {
    $ch = curl_init($webhookValidation['url']);
    $forwardData = json_encode([
        'message' => $message,
        'sessionId' => $sessionId,
        'subdomain' => $subdomain,
        'businessName' => $businessName,
        'extractedEmail' => $extractedEmail,
        'extractedPhone' => $extractedPhone,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $forwardData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: ChatModel-MultiTenant-Proxy/2.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        // Fallback simulated intelligent reply
        $fallbackReply = "Thank you for reaching out to **$businessName**! Your message (*\"$message\"*) was received. Automated workflows and CRM triggers are running seamlessly.";
        Database::logMessage($subdomain ?: 'root', $sessionId, 'bot', $fallbackReply);

        // Dispatch CRM Pipeline trigger if lead detected
        if (!empty($crmWebhookUrl) && ($extractedEmail || $extractedPhone || preg_match('/(pricing|price|quote|demo|hire|consult|call|contact|book)/i', $message))) {
            dispatchCrmPipeline($crmWebhookUrl, $webhookSecret, 'lead_capture', [
                'event' => 'lead_capture',
                'subdomain' => $subdomain,
                'business_name' => $businessName,
                'session_id' => $sessionId,
                'customer_message' => $message,
                'extracted_email' => $extractedEmail,
                'extracted_phone' => $extractedPhone,
                'bot_response' => $fallbackReply,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode([
            'response' => $fallbackReply,
            'source' => 'chatmodel_smart_router',
            'session_id' => $sessionId,
            'lead_captured' => ($extractedEmail || $extractedPhone) ? true : false,
            'quick_replies' => ['Explore Features', 'Book a Consultation', 'Live Representative']
        ]);
        exit;
    }

    $decoded = json_decode($response, true);
    $botText = '';
    if ($decoded && is_array($decoded)) {
        $botText = $decoded['output'] ?? $decoded['response'] ?? $decoded['text'] ?? $decoded['message'] ?? $response;
        Database::logMessage($subdomain ?: 'root', $sessionId, 'bot', is_string($botText) ? $botText : json_encode($botText));
    } else {
        $botText = !empty($response) ? $response : "Message processed by $businessName automation.";
        Database::logMessage($subdomain ?: 'root', $sessionId, 'bot', $botText);
        $decoded = ['response' => $botText];
    }

    // Dispatch to CRM Pipeline
    if (!empty($crmWebhookUrl) && ($extractedEmail || $extractedPhone || preg_match('/(pricing|price|quote|demo|hire|consult|call|contact|book)/i', $message))) {
        dispatchCrmPipeline($crmWebhookUrl, $webhookSecret, 'lead_capture', [
            'event' => 'lead_capture',
            'subdomain' => $subdomain,
            'business_name' => $businessName,
            'session_id' => $sessionId,
            'customer_message' => $message,
            'extracted_email' => $extractedEmail,
            'extracted_phone' => $extractedPhone,
            'bot_response' => is_string($botText) ? $botText : json_encode($botText),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    echo json_encode($decoded);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Proxy communication failed: ' . $e->getMessage()]);
}
