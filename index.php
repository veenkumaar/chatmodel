<?php
/**
 * ChatModel Multi-Tenant SaaS Entrypoint & Dynamic Domain Router
 * - Root Domain (chatmodel.in): High-Converting Commercial SaaS Landing Page
 * - Subdomain (e.g. aditya.chatmodel.in): Dedicated Branded Client AI Assistant Interface
 * - Admin Console: Secured at /login.php
 */

require_once __DIR__ . '/db/database.php';
require_once __DIR__ . '/db/security.php';

Security::startSecureSession();

// For assistant view, allow iframe embedding from customer websites, for root allow standard
$isSubdomainRequest = !empty($_GET['subdomain']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.') !== false);
Security::applySecurityHeaders($isSubdomainRequest);

// Detect host and subdomain
$rawHost = $_SERVER['HTTP_HOST'] ?? 'chatmodel.in';
$hostParts = explode(':', $rawHost);
$host = strtolower($hostParts[0]);

// Allow ?subdomain= query param override for local testing / instant previews
$subdomainOverride = $_GET['subdomain'] ?? null;

$subdomain = null;
if ($subdomainOverride) {
    $subdomain = Security::sanitizeSlug($subdomainOverride);
} elseif (preg_match('/^([a-z0-9-]+)\.chatmodel\.in$/i', $host, $matches)) {
    $matched = strtolower($matches[1]);
    if ($matched !== 'www' && $matched !== 'n8n' && $matched !== 'admin') {
        $subdomain = $matched;
    }
} elseif ($host !== 'chatmodel.in' && $host !== 'www.chatmodel.in' && $host !== 'localhost' && $host !== '127.0.0.1') {
    // Custom dedicated CNAME domain resolution (e.g., chat.clientbrand.com)
    $customTenant = Database::getTenantByCustomDomain($host);
    if ($customTenant) {
        $subdomain = $customTenant['subdomain'];
    }
}

// -------------------------------------------------------------
// SCENARIO 1: SUBDOMAIN TENANT VIEW (e.g., aditya.chatmodel.in)
// -------------------------------------------------------------
if (!empty($subdomain)) {
    $tenant = Database::getTenantBySubdomain($subdomain);

    // Check if tenant exists
    if (!$tenant) {
        http_response_code(404);
        require __DIR__ . '/views/errors/404.php';
        exit;
    }

    $isActive = (int) $tenant['is_active'] === 1;
    $themeColor = $tenant['theme_color'] ?? '#4f46e5';
    $businessName = $tenant['business_name'] ?? 'AI Assistant';
    $welcomeMessage = $tenant['welcome_message'] ?? 'Hello! How can I assist you today?';

    // If disabled / inactive
    if (!$isActive) {
        http_response_code(403);
        require __DIR__ . '/views/errors/403.php';
        exit;
    }

    // Tenant Active - Render dedicated chat UI
    require __DIR__ . '/views/assistant/chat_interface.php';
    exit;
}

// -------------------------------------------------------------
// SCENARIO 2: ROOT DOMAIN (chatmodel.in) - MODERN SAAS MARKETING
// -------------------------------------------------------------
$isAdmin = isset($_SESSION['chatmodel_admin_logged_in']) && $_SESSION['chatmodel_admin_logged_in'] === true;
$adminUser = $_SESSION['chatmodel_admin_user'] ?? '';

require __DIR__ . '/views/landing/index.php';