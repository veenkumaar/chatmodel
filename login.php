<?php
/**
 * Unified Authentication & Multi-Tenant Management Portal
 * Supports Role-Based Views: Global Admin Portal & Dedicated Client Workspace Portal
 */
require_once __DIR__ . '/db/database.php';
require_once __DIR__ . '/db/security.php';

Security::startSecureSession();
Security::applySecurityHeaders(false); // Disallow iframe framing on admin/login portal to prevent clickjacking
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

$error = null;
$success = null;

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Process login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    // CSRF Token validation
    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::validateCsrfToken($csrf)) {
        $error = 'Security session expired or invalid CSRF token. Please refresh and try again.';
    } elseif (!Security::checkRateLimit('portal_login', 8, 60)) {
        $error = 'Too many login attempts. Please wait 1 minute before trying again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } elseif (Database::verifyAdmin($username, $password)) {
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['chatmodel_logged_in'] = true;
            $_SESSION['chatmodel_role'] = 'admin';
            $_SESSION['chatmodel_admin_logged_in'] = true;
            $_SESSION['chatmodel_admin_user'] = $username;
            header('Location: /login.php');
            exit;
        } else {
            $tenantUser = Database::verifyTenantUser($username, $password);
            if ($tenantUser) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['chatmodel_logged_in'] = true;
                $_SESSION['chatmodel_role'] = 'user';
                $_SESSION['chatmodel_tenant_subdomain'] = $tenantUser['subdomain'];
                $_SESSION['chatmodel_user'] = $tenantUser['business_name'];
                header('Location: /login.php');
                exit;
            } else {
                $error = 'Invalid credentials. Please enter a valid Admin username or Tenant subdomain with your password.';
            }
        }
    }
}

$isAdmin = (isset($_SESSION['chatmodel_role']) && $_SESSION['chatmodel_role'] === 'admin') ||
    (isset($_SESSION['chatmodel_admin_logged_in']) && $_SESSION['chatmodel_admin_logged_in'] === true);
$isUser = isset($_SESSION['chatmodel_role']) && $_SESSION['chatmodel_role'] === 'user' && !empty($_SESSION['chatmodel_tenant_subdomain']);
$isLoggedIn = $isAdmin || $isUser;
$role = $isAdmin ? 'admin' : ($isUser ? 'user' : null);

$adminUser = $_SESSION['chatmodel_admin_user'] ?? 'Admin';

if ($isAdmin) {
    $allTenants = Database::getAllTenantsWithStats();
    $allInquiries = Database::getAllInquiries();
    $newInquiriesCount = Database::getNewInquiriesCount();
    require __DIR__ . '/views/admin/dashboard.php';
    exit;
}

if ($isUser) {
    $subdomain = $_SESSION['chatmodel_tenant_subdomain'];
    $currentTenant = Database::getTenantBySubdomain($subdomain);
    if (!$currentTenant) {
        $_SESSION = [];
        session_destroy();
        header('Location: /login.php');
        exit;
    }
    $tenantStats = Database::getTenantConversationStats($subdomain);
    $tenantLogs = Database::getTenantChatHistory($subdomain, 50);
    require __DIR__ . '/views/tenant/workspace.php';
    exit;
}

// Guest view - Render login form
require __DIR__ . '/views/auth/login_form.php';