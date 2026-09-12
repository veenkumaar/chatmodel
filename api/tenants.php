<?php
/**
 * REST API for Tenant Management & Authentication Protection
 */

require_once __DIR__ . '/../db/database.php';
require_once __DIR__ . '/../db/security.php';

Security::startSecureSession();
Security::applySecurityHeaders(false);

// Rate Limit API endpoints: 60 requests per minute per IP
if (!Security::checkRateLimit('api_tenants', 60, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please slow down.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function isAdminLoggedIn(): bool {
    return (isset($_SESSION['chatmodel_role']) && $_SESSION['chatmodel_role'] === 'admin') ||
           (isset($_SESSION['chatmodel_admin_logged_in']) && $_SESSION['chatmodel_admin_logged_in'] === true);
}

function isUserLoggedIn(): bool {
    return isset($_SESSION['chatmodel_role']) && $_SESSION['chatmodel_role'] === 'user' && !empty($_SESSION['chatmodel_tenant_subdomain']);
}

function getCurrentUserSubdomain(): ?string {
    return $_SESSION['chatmodel_tenant_subdomain'] ?? null;
}

try {
    if ($method === 'GET') {
        // Check auth status endpoint
        if (isset($_GET['action']) && $_GET['action'] === 'check_auth') {
            echo json_encode([
                'logged_in' => isAdminLoggedIn() || isUserLoggedIn(),
                'role' => isAdminLoggedIn() ? 'admin' : (isUserLoggedIn() ? 'user' : null),
                'username' => $_SESSION['chatmodel_admin_user'] ?? $_SESSION['chatmodel_user'] ?? null,
                'subdomain' => getCurrentUserSubdomain()
            ]);
            exit;
        }

        // Public endpoint: Check subdomain availability
        if (isset($_GET['action']) && $_GET['action'] === 'check_subdomain') {
            $checkSub = strtolower(trim($_GET['subdomain'] ?? ''));
            $res = Database::isSubdomainAvailable($checkSub);
            echo json_encode($res);
            exit;
        }

        // Get Chat Conversation History / Logs for Tenant
        if (isset($_GET['action']) && $_GET['action'] === 'get_logs') {
            $subdomain = strtolower(trim($_GET['subdomain'] ?? ''));
            
            // Check auth: Admin can view any, user can only view their own
            if (!isAdminLoggedIn()) {
                if (!isUserLoggedIn() || getCurrentUserSubdomain() !== $subdomain) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Unauthorized']);
                    exit;
                }
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $logs = Database::getTenantChatHistory($subdomain, $limit);
            $stats = Database::getTenantConversationStats($subdomain);
            echo json_encode([
                'success' => true,
                'subdomain' => $subdomain,
                'stats' => $stats,
                'logs' => $logs
            ]);
            exit;
        }

        // Admin Endpoint: Get All Inquiries
        if (isset($_GET['action']) && $_GET['action'] === 'get_inquiries') {
            if (!isAdminLoggedIn()) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required']);
                exit;
            }
            $inquiries = Database::getAllInquiries();
            $newCount = Database::getNewInquiriesCount();
            echo json_encode([
                'success' => true,
                'inquiries' => $inquiries,
                'new_count' => $newCount
            ]);
            exit;
        }

        // List all tenants with stats or get specific tenant by subdomain
        if (isset($_GET['subdomain'])) {
            $tenant = Database::getTenantBySubdomain($_GET['subdomain']);
            if (!$tenant) {
                http_response_code(404);
                echo json_encode(['error' => 'Tenant not found']);
                exit;
            }
            $tenant['stats'] = Database::getTenantConversationStats($_GET['subdomain']);
            echo json_encode(['success' => true, 'tenant' => $tenant]);
        } else {
            if (!isAdminLoggedIn()) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required to list all workspaces']);
                exit;
            }
            $tenants = Database::getAllTenantsWithStats();
            echo json_encode(['success' => true, 'tenants' => $tenants]);
        }
        exit;
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        $action = $data['action'] ?? 'create';

        // Public Subdomain Availability Check via POST as well
        if ($action === 'check_subdomain') {
            $checkSub = strtolower(trim($data['subdomain'] ?? ''));
            $res = Database::isSubdomainAvailable($checkSub);
            echo json_encode($res);
            exit;
        }

        // Public Lead & Business Inquiry Submission Endpoint
        if ($action === 'submit_inquiry') {
            // Anti-spam Rate Limit: 10 inquiries per 10 minutes per IP
            if (!Security::checkRateLimit('submit_inquiry', 10, 600)) {
                http_response_code(429);
                echo json_encode(['error' => 'Too many submissions. Please wait a few minutes before submitting again.']);
                exit;
            }

            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $phone = trim($data['phone'] ?? '');
            $company = trim($data['company'] ?? '');
            $plan = trim($data['plan'] ?? 'professional');
            $message = trim($data['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Name, valid email, and your inquiry message are required.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Please provide a valid email address.']);
                exit;
            }

            $ip = Security::getClientIp();
            $saved = Database::createInquiry($name, $email, $phone, $company, $plan, $message, $ip);

            if ($saved) {
                echo json_encode([
                    'success' => true,
                    'message' => "Thank you, $name! Your inquiry has been received. Our team will get back to you shortly."
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to record inquiry. Please try again.']);
            }
            exit;
        }

        // Admin-only: Update Inquiry Status / Mark Read
        if ($action === 'update_inquiry_status' || $action === 'mark_inquiry_read') {
            if (!isAdminLoggedIn()) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required.']);
                exit;
            }
            $id = (int)($data['id'] ?? 0);
            $status = trim($data['status'] ?? 'read');
            $updated = Database::updateInquiryStatus($id, $status);
            echo json_encode(['success' => $updated, 'message' => 'Inquiry status updated.']);
            exit;
        }

        // Admin-only: Delete Inquiry
        if ($action === 'delete_inquiry') {
            if (!isAdminLoggedIn()) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required.']);
                exit;
            }
            $id = (int)($data['id'] ?? 0);
            $deleted = Database::deleteInquiry($id);
            echo json_encode(['success' => $deleted, 'message' => 'Inquiry record removed.']);
            exit;
        }

        // 1. Unified Authentication (Admin & User)
        if ($action === 'login') {
            // Anti-Brute Force Protection: 8 attempts per minute per IP
            if (!Security::checkRateLimit('login_attempt', 8, 60)) {
                http_response_code(429);
                echo json_encode(['error' => 'Too many failed login attempts. Please wait 1 minute before trying again.']);
                exit;
            }

            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');

            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password are required']);
                exit;
            }

            // A. Check Admin Credentials
            if (Database::verifyAdmin($username, $password)) {
                session_regenerate_id(true); // Prevent Session Fixation
                $_SESSION['chatmodel_logged_in'] = true;
                $_SESSION['chatmodel_role'] = 'admin';
                $_SESSION['chatmodel_admin_logged_in'] = true;
                $_SESSION['chatmodel_admin_user'] = $username;
                echo json_encode([
                    'success' => true,
                    'role' => 'admin',
                    'message' => 'Admin login successful',
                    'username' => $username,
                    'redirect' => '/login.php'
                ]);
                exit;
            }

            // B. Check Tenant User Credentials
            $tenant = Database::verifyTenantUser($username, $password);
            if ($tenant) {
                session_regenerate_id(true); // Prevent Session Fixation
                $_SESSION['chatmodel_logged_in'] = true;
                $_SESSION['chatmodel_role'] = 'user';
                $_SESSION['chatmodel_tenant_subdomain'] = $tenant['subdomain'];
                $_SESSION['chatmodel_user'] = $tenant['business_name'];
                echo json_encode([
                    'success' => true,
                    'role' => 'user',
                    'message' => 'Client portal login successful',
                    'subdomain' => $tenant['subdomain'],
                    'business_name' => $tenant['business_name'],
                    'redirect' => '/login.php'
                ]);
                exit;
            }

            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials. Please check your username/subdomain and password.']);
            exit;
        }

        if ($action === 'logout') {
            $_SESSION = [];
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            exit;
        }

        // 2. Action Authorization Checks
        $isUser = isUserLoggedIn();
        $isAdmin = isAdminLoggedIn();
        $userSubdomain = getCurrentUserSubdomain();

        if (!$isAdmin && !$isUser) {
            http_response_code(403);
            echo json_encode([
                'error' => 'Unauthorized. Please login to your workspace or admin portal.',
                'unauthorized' => true
            ]);
            exit;
        }

        // Action: Update password
        if ($action === 'update_password') {
            $subdomain = trim($data['subdomain'] ?? '');
            $newPassword = trim($data['new_password'] ?? '');

            if (empty($subdomain) || empty($newPassword)) {
                http_response_code(400);
                echo json_encode(['error' => 'Subdomain and new password are required.']);
                exit;
            }

            if (!$isAdmin && ($userSubdomain !== strtolower($subdomain))) {
                http_response_code(403);
                echo json_encode(['error' => 'You can only update your own password.']);
                exit;
            }

            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least 6 characters long.']);
                exit;
            }

            $updated = Database::updateTenantPassword($subdomain, $newPassword);
            echo json_encode([
                'success' => $updated,
                'message' => 'Password updated successfully!'
            ]);
            exit;
        }

        // Admin-only: Toggle active status
        if ($action === 'toggle_status') {
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required.']);
                exit;
            }

            $subdomain = $data['subdomain'] ?? '';
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 0;

            if (empty($subdomain)) {
                http_response_code(400);
                echo json_encode(['error' => 'Subdomain is required']);
                exit;
            }

            $success = Database::updateTenantStatus($subdomain, $isActive);
            echo json_encode([
                'success' => $success,
                'message' => "Subdomain '$subdomain' status updated to " . ($isActive ? 'Active (Enabled)' : 'Disabled')
            ]);
            exit;
        }

        // Admin-only: Create new tenant
        if ($action === 'create') {
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required to provision new workspaces.']);
                exit;
            }

            $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', trim($data['subdomain'] ?? '')));
            $customDomain = strtolower(trim($data['custom_domain'] ?? ''));
            $businessName = trim($data['business_name'] ?? '');
            $webhookUrl = trim($data['webhook_url'] ?? '');
            $welcomeMessage = trim($data['welcome_message'] ?? 'Hello! How can we help your business today?');
            $themeColor = trim($data['theme_color'] ?? '#4f46e5');
            $plan = trim($data['plan'] ?? 'starter');
            $monthlyLimit = isset($data['monthly_limit']) ? (int)$data['monthly_limit'] : 2500;
            $crmWebhookUrl = trim($data['crm_webhook_url'] ?? '');
            $webhookSecret = trim($data['webhook_secret'] ?? '');
            $crmEvents = trim($data['crm_events'] ?? 'lead_capture,escalation');
            $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            if (empty($subdomain) || empty($businessName) || empty($webhookUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'Subdomain, business name, and automation webhook URL are required.']);
                exit;
            }

            $avail = Database::isSubdomainAvailable($subdomain);
            if (!$avail['available']) {
                http_response_code(409);
                echo json_encode(['error' => $avail['reason']]);
                exit;
            }

            if (!empty($customDomain)) {
                $dup = Database::getTenantByCustomDomain($customDomain);
                if ($dup) {
                    http_response_code(409);
                    echo json_encode(['error' => "Custom domain '$customDomain' is already assigned to another tenant."]);
                    exit;
                }
            }

            $created = Database::createTenant($subdomain, $businessName, $webhookUrl, $welcomeMessage, $themeColor, $plan, $monthlyLimit, $crmWebhookUrl, $webhookSecret, $crmEvents, $isActive, $customDomain);
            if ($created) {
                echo json_encode([
                    'success' => true,
                    'message' => "Dedicated Subdomain '{$subdomain}.chatmodel.in' provisioned successfully!",
                    'tenant' => Database::getTenantBySubdomain($subdomain)
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create tenant in database.']);
            }
            exit;
        }

        // Update Tenant: Admin OR Client User on their own subdomain
        if ($action === 'update') {
            $subdomain = trim($data['subdomain'] ?? '');
            if (empty($subdomain)) {
                http_response_code(400);
                echo json_encode(['error' => 'Subdomain is required']);
                exit;
            }

            if (!$isAdmin && ($userSubdomain !== strtolower($subdomain))) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized to update this workspace.']);
                exit;
            }

            $existing = Database::getTenantBySubdomain($subdomain);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Tenant not found.']);
                exit;
            }

            $customDomain = strtolower(trim($data['custom_domain'] ?? ''));
            $businessName = trim($data['business_name'] ?? $existing['business_name']);
            $webhookUrl = trim($data['webhook_url'] ?? $existing['webhook_url']);
            $welcomeMessage = trim($data['welcome_message'] ?? $existing['welcome_message']);
            $themeColor = trim($data['theme_color'] ?? $existing['theme_color']);
            
            // Non-admins keep their plan & active status unless changed by admin
            $plan = $isAdmin ? trim($data['plan'] ?? $existing['plan']) : $existing['plan'];
            $monthlyLimit = $isAdmin && isset($data['monthly_limit']) ? (int)$data['monthly_limit'] : (int)$existing['monthly_limit'];
            $isActive = $isAdmin && isset($data['is_active']) ? (int)$data['is_active'] : (int)$existing['is_active'];

            $crmWebhookUrl = trim($data['crm_webhook_url'] ?? $existing['crm_webhook_url'] ?? '');
            $webhookSecret = trim($data['webhook_secret'] ?? $existing['webhook_secret'] ?? '');
            $crmEvents = trim($data['crm_events'] ?? $existing['crm_events'] ?? 'lead_capture,escalation');

            if (!empty($customDomain)) {
                $dup = Database::getTenantByCustomDomain($customDomain);
                if ($dup && strtolower($dup['subdomain']) !== strtolower($subdomain)) {
                    http_response_code(409);
                    echo json_encode(['error' => "Custom domain '$customDomain' is already assigned to another tenant ({$dup['subdomain']})."]);
                    exit;
                }
            }

            $updated = Database::updateTenant($subdomain, $businessName, $webhookUrl, $welcomeMessage, $themeColor, $plan, $monthlyLimit, $crmWebhookUrl, $webhookSecret, $crmEvents, $isActive, $customDomain);
            echo json_encode(['success' => $updated, 'message' => 'Dedicated subdomain and custom domain settings updated successfully.']);
            exit;
        }

        if ($action === 'verify_custom_domain') {
            $customDomain = strtolower(trim($data['custom_domain'] ?? ''));
            $subdomain = trim($data['subdomain'] ?? '');

            if (empty($customDomain)) {
                http_response_code(400);
                echo json_encode(['error' => 'Custom domain is required for DNS verification.']);
                exit;
            }

            // Perform DNS CNAME and A record inspection
            $cnameRecords = @dns_get_record($customDomain, DNS_CNAME) ?: [];
            $aRecords = @dns_get_record($customDomain, DNS_A) ?: [];

            $cnameTarget = null;
            $isCnameMatched = false;
            foreach ($cnameRecords as $rec) {
                if (isset($rec['target'])) {
                    $cnameTarget = strtolower($rec['target']);
                    if (str_contains($cnameTarget, 'chatmodel.in') || str_contains($cnameTarget, $subdomain)) {
                        $isCnameMatched = true;
                        break;
                    }
                }
            }

            $aTarget = null;
            if (!empty($aRecords)) {
                $aTarget = $aRecords[0]['ip'] ?? null;
            }

            echo json_encode([
                'success' => true,
                'domain' => $customDomain,
                'cname_matched' => $isCnameMatched,
                'detected_cname' => $cnameTarget,
                'detected_ip' => $aTarget,
                'required_cname' => "{$subdomain}.chatmodel.in",
                'status' => $isCnameMatched ? 'Configured & Verified' : 'DNS Propagation Pending',
                'instructions' => "Create a DNS CNAME record in your registrar (Cloudflare, GoDaddy, Namecheap) pointing '$customDomain' to '{$subdomain}.chatmodel.in' or 'chatmodel.in'."
            ]);
            exit;
        }

        if ($action === 'test_crm_pipeline') {
            $crmUrl = trim($data['crm_webhook_url'] ?? '');
            $secret = trim($data['webhook_secret'] ?? '');
            $subdomain = Security::sanitizeSlug($data['subdomain'] ?? 'demo');

            if (empty($crmUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'Please provide a CRM Webhook URL to test.']);
                exit;
            }

            // SSRF Check
            $val = Security::validateExternalUrl($crmUrl);
            if (!$val['valid']) {
                http_response_code(400);
                echo json_encode(['error' => 'Security Error: ' . $val['error']]);
                exit;
            }

            $testPayload = json_encode([
                'event' => 'crm_pipeline_test',
                'subdomain' => $subdomain,
                'business_name' => 'ChatModel Verification Agent',
                'session_id' => 'test_' . bin2hex(random_bytes(8)),
                'customer_name' => 'Security Test Lead',
                'customer_email' => 'lead.test@chatmodel.in',
                'customer_phone' => '+91 9876543210',
                'customer_message' => 'Interested in enterprise automated chat pipeline setup for our CRM.',
                'timestamp' => date('Y-m-d H:i:s'),
                'is_test' => true
            ]);

            $startTime = microtime(true);
            $ch = curl_init($val['url']);
            $headers = [
                'Content-Type: application/json',
                'X-ChatModel-Event: crm_pipeline_test',
                'User-Agent: ChatModel-Pipeline-Tester/2.0'
            ];
            if (!empty($secret)) {
                $headers[] = 'Authorization: Bearer ' . str_replace(["\r", "\n"], '', $secret);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $testPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Prevent open-redirect SSRF

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $latency = round((microtime(true) - $startTime) * 1000);
            curl_close($ch);

            if ($error) {
                echo json_encode([
                    'success' => false,
                    'error' => "CRM Endpoint unreachable: $error",
                    'latency_ms' => $latency
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'http_code' => $httpCode,
                    'latency_ms' => $latency,
                    'message' => "CRM Webhook Pipeline responded with HTTP $httpCode in {$latency}ms.",
                    'response_preview' => mb_substr($response, 0, 150)
                ]);
            }
            exit;
        }

        // Admin-only: Delete Tenant
        if ($action === 'delete') {
            if (!$isAdmin) {
                http_response_code(403);
                echo json_encode(['error' => 'Admin authorization required to delete workspaces.']);
                exit;
            }

            $subdomain = trim($data['subdomain'] ?? '');
            $deleted = Database::deleteTenant($subdomain);
            echo json_encode(['success' => $deleted, 'message' => "Tenant '$subdomain' removed."]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
