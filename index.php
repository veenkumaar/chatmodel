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

// RFC 9727 /.well-known/api-catalog Handler
$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestUriPath === '/.well-known/api-catalog' || $requestUriPath === '/.well-known/api-catalog.json') {
    header('Content-Type: application/linkset+json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $catalogFile = __DIR__ . '/.well-known/api-catalog';
    if (file_exists($catalogFile)) {
        readfile($catalogFile);
    } else {
        echo json_encode([
            "linkset" => [
                [
                    "anchor" => "https://chatmodel.in/api/",
                    "service-desc" => [["href" => "https://chatmodel.in/api/openapi.json", "type" => "application/openapi+json"]],
                    "service-doc" => [["href" => "https://chatmodel.in/faq.php", "type" => "text/html"]],
                    "status" => [["href" => "https://chatmodel.in/api/health.php", "type" => "application/json"]]
                ]
            ]
        ]);
    }
    exit;
}

// RFC 8414 & OpenID Connect Discovery Handlers
if ($requestUriPath === '/.well-known/openid-configuration') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $oidcFile = __DIR__ . '/.well-known/openid-configuration';
    if (file_exists($oidcFile)) {
        readfile($oidcFile);
    }
    exit;
}

if ($requestUriPath === '/.well-known/oauth-authorization-server') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $oauthFile = __DIR__ . '/.well-known/oauth-authorization-server';
    if (file_exists($oauthFile)) {
        readfile($oauthFile);
    }
    exit;
}

if ($requestUriPath === '/.well-known/oauth-protected-resource' || $requestUriPath === '/.well-known/oauth-protected-resource.json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $protResFile = __DIR__ . '/.well-known/oauth-protected-resource';
    if (file_exists($protResFile)) {
        readfile($protResFile);
    } else {
        echo json_encode([
            "resource" => "https://chatmodel.in/api",
            "authorization_servers" => ["https://chatmodel.in"],
            "scopes_supported" => ["openid", "profile", "email", "chat", "tenants:read", "tenants:write"]
        ]);
    }
    exit;
}

if ($requestUriPath === '/auth.md') {
    $authMdFile = __DIR__ . '/auth.md';
    if (file_exists($authMdFile)) {
        Security::respondWithMarkdown(file_get_contents($authMdFile));
    }
    exit;
}

if ($requestUriPath === '/.well-known/jwks.json') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $jwksFile = __DIR__ . '/.well-known/jwks.json';
    if (file_exists($jwksFile)) {
        readfile($jwksFile);
    }
    exit;
}

// SEP-1649 MCP Server Card Handler
if ($requestUriPath === '/.well-known/mcp/server-card.json' || $requestUriPath === '/.well-known/mcp/server-card') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $mcpFile = __DIR__ . '/.well-known/mcp/server-card.json';
    if (file_exists($mcpFile)) {
        readfile($mcpFile);
    }
    exit;
}

// Agent Skills Discovery Index Handler (Agent Skills RFC v0.2.0)
if ($requestUriPath === '/.well-known/agent-skills/index.json' || $requestUriPath === '/.well-known/agent-skills' || $requestUriPath === '/.well-known/agent-skills/') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $skillsFile = __DIR__ . '/.well-known/agent-skills/index.json';
    if (file_exists($skillsFile)) {
        readfile($skillsFile);
    }
    exit;
}

if ($requestUriPath === '/.well-known/agent-skills/chatmodel-assistant/SKILL.md') {
    header('Content-Type: text/markdown; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $skillMd = __DIR__ . '/.well-known/agent-skills/chatmodel-assistant/SKILL.md';
    if (file_exists($skillMd)) {
        readfile($skillMd);
    }
    exit;
}

// ARD (Agentic Resource Discovery) ai-catalog.json Handler
if ($requestUriPath === '/.well-known/ai-catalog.json' || $requestUriPath === '/.well-known/ai-catalog') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    $ardFile = __DIR__ . '/.well-known/ai-catalog.json';
    if (file_exists($ardFile)) {
        readfile($ardFile);
    }
    exit;
}

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
}

// -------------------------------------------------------------
// SCENARIO 1: SUBDOMAIN TENANT VIEW (e.g., aditya.chatmodel.in)
// -------------------------------------------------------------
if (!empty($subdomain)) {
    // Send HTTP-level anti-indexing header for all tenant subdomains
    header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true);

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

    $chatAccessMode = $tenant['chat_access_mode'] ?? 'public';
    $isChatAuthenticated = !empty($_SESSION['chatmodel_chat_authenticated_' . $subdomain]);

    // Markdown Content Negotiation for Agents
    if (Security::wantsMarkdown()) {
        $md = "# " . $businessName . " AI Assistant\n\n";
        $md .= "> " . $welcomeMessage . "\n\n";
        $md .= "- **Subdomain**: " . $subdomain . ".chatmodel.in\n";
        $md .= "- **Access Mode**: " . $chatAccessMode . "\n";
        $md .= "- **API Endpoint**: https://chatmodel.in/api/chat.php\n";
        $md .= "- **API Docs**: https://chatmodel.in/api/openapi.json\n\n";
        $md .= "To interact with this assistant via API, send a POST request with JSON `{\"message\": \"...\", \"subdomain\": \"" . $subdomain . "\", \"sessionId\": \"...\"}` to `https://chatmodel.in/api/chat.php`.\n";
        Security::respondWithMarkdown($md);
    }

    // Tenant Active - Render dedicated chat UI
    require __DIR__ . '/views/assistant/chat_interface.php';
    exit;
}

// -------------------------------------------------------------
// SCENARIO 2: ROOT DOMAIN (chatmodel.in) - MODERN SAAS MARKETING
// -------------------------------------------------------------
$isAdmin = isset($_SESSION['chatmodel_admin_logged_in']) && $_SESSION['chatmodel_admin_logged_in'] === true;
$adminUser = $_SESSION['admin_user'] ?? '';

// RFC 8288 & RFC 9727 Agent Discovery Link response headers
header('Link: </.well-known/api-catalog>; rel="api-catalog", </api/openapi.json>; rel="service-desc"; type="application/openapi+json", </faq.php>; rel="service-doc"');

// Markdown Content Negotiation for Agents (Accept: text/markdown)
if (Security::wantsMarkdown()) {
    $homepageMd = file_exists(__DIR__ . '/llms.txt') ? file_get_contents(__DIR__ . '/llms.txt') : "# ChatModel\n\nEnterprise Conversational Intelligence Platform";
    Security::respondWithMarkdown($homepageMd);
}

require __DIR__ . '/views/landing/index.php';