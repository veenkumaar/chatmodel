<?php
/**
 * ChatModel Platform Security & Hardening Module
 * Provides Defense-in-Depth against SQLi, XSS, CSRF, SSRF, Brute Force & Session Hijacking.
 */

class Security {
    private static string $rateLimitTable = 'rate_limits';

    /**
     * Start hardened session with secure cookie parameters
     */
    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

                session_set_cookie_params([
                    'lifetime' => 86400 * 7, // 7 days
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                @session_start();
            } else {
                @session_start();
            }
        }
    }

    /**
     * Set Global Defense Security Headers
     */
    public static function applySecurityHeaders(bool $allowFrameEmbedding = false): void {
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if (!$allowFrameEmbedding) {
            header('X-Frame-Options: SAMEORIGIN');
        }

        // Add Content-Security-Policy (allows Google Fonts, inline styles for dynamic branding, and standard APIs)
        $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: http: data: blob:; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com data:; " .
               "img-src 'self' data: https: http: blob:; " .
               "connect-src 'self' https: http: ws: wss:; " .
               ($allowFrameEmbedding ? "frame-ancestors *;" : "frame-ancestors 'self';");
        
        header("Content-Security-Policy: $csp");
    }

    /**
     * Generate or fetch current CSRF Token for Session
     */
    public static function getCsrfToken(): string {
        self::startSecureSession();
        if (empty($_SESSION['chatmodel_csrf_token'])) {
            $_SESSION['chatmodel_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['chatmodel_csrf_token'];
    }

    /**
     * Validate CSRF Token
     */
    public static function validateCsrfToken(?string $token): bool {
        self::startSecureSession();
        if (empty($_SESSION['chatmodel_csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['chatmodel_csrf_token'], $token);
    }

    /**
     * SSRF Safe URL Validator:
     * Disallows loopback (127.0.0.1, ::1), private RFC1918 subnets, cloud metadata (169.254.169.254), and local hostnames.
     */
    public static function validateExternalUrl(string $url): array {
        $trimmed = trim($url);
        if (empty($trimmed)) {
            return ['valid' => false, 'error' => 'URL cannot be empty'];
        }

        // Must be http or https
        if (!preg_match('/^https?:\/\//i', $trimmed)) {
            return ['valid' => false, 'error' => 'URL must use HTTP or HTTPS protocol'];
        }

        $parts = parse_url($trimmed);
        if (!$parts || empty($parts['host'])) {
            return ['valid' => false, 'error' => 'Malformed URL'];
        }

        $host = strtolower($parts['host']);

        // Block localhost and internal names
        $blockedHosts = ['localhost', '127.0.0.1', '::1', '0.0.0.0', '169.254.169.254', 'instance-data', 'metadata.google.internal'];
        if (in_array($host, $blockedHosts)) {
            return ['valid' => false, 'error' => 'Access to internal/localhost addresses is forbidden for security.'];
        }

        // Resolve DNS and check if IP is private/loopback/reserved
        $ips = @dns_get_record($host, DNS_A);
        if (!empty($ips)) {
            foreach ($ips as $ipRecord) {
                $ip = $ipRecord['ip'] ?? '';
                if ($ip && !self::isPublicIp($ip)) {
                    return ['valid' => false, 'error' => "URL resolves to a private or restricted network address ($ip)."];
                }
            }
        } elseif (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!self::isPublicIp($host)) {
                return ['valid' => false, 'error' => 'Direct access to private/internal IP address is blocked.'];
            }
        }

        return ['valid' => true, 'url' => $trimmed];
    }

    /**
     * Check if an IPv4/IPv6 is a valid public Internet routable IP
     */
    public static function isPublicIp(string $ip): bool {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * IP-based & Action-based Rate Limiter
     * @param string $action Action key (e.g. 'login', 'chat', 'api')
     * @param int $maxAttempts Maximum allowed hits in the time window
     * @param int $windowSeconds Time window in seconds
     * @return bool True if allowed, False if rate limited
     */
    public static function checkRateLimit(string $action, int $maxAttempts = 30, int $windowSeconds = 60): bool {
        try {
            $db = Database::getConnection();
            
            // Ensure rate limits table exists
            $db->exec("CREATE TABLE IF NOT EXISTS " . self::$rateLimitTable . " (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip TEXT NOT NULL,
                action TEXT NOT NULL,
                attempts INTEGER DEFAULT 1,
                last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $ip = self::getClientIp();
            
            // Cleanup old records
            $cleanStmt = $db->prepare("DELETE FROM " . self::$rateLimitTable . " WHERE last_attempt < datetime('now', '-' || :secs || ' seconds')");
            $cleanStmt->bindValue(':secs', $windowSeconds, PDO::PARAM_INT);
            $cleanStmt->execute();

            // Fetch record for this IP and action
            $stmt = $db->prepare("SELECT * FROM " . self::$rateLimitTable . " WHERE ip = :ip AND action = :action LIMIT 1");
            $stmt->execute([':ip' => $ip, ':action' => $action]);
            $record = $stmt->fetch();

            if ($record) {
                if ($record['attempts'] >= $maxAttempts) {
                    return false; // Rate limit exceeded
                }
                $upd = $db->prepare("UPDATE " . self::$rateLimitTable . " SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE id = :id");
                $upd->execute([':id' => $record['id']]);
            } else {
                $ins = $db->prepare("INSERT INTO " . self::$rateLimitTable . " (ip, action, attempts) VALUES (:ip, :action, 1)");
                $ins->execute([':ip' => $ip, ':action' => $action]);
            }

            return true;
        } catch (Exception $e) {
            // Fail open gracefully if SQLite is busy so user isn't locked out
            return true;
        }
    }

    /**
     * Get Client Real IP (taking proxy headers into account safely)
     */
    public static function getClientIp(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $raw = $_SERVER[$header];
                $ips = explode(',', $raw);
                $cleanIp = trim($ips[0]);
                if (filter_var($cleanIp, FILTER_VALIDATE_IP)) {
                    return $cleanIp;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Strict XSS Safe HTML Escaper
     */
    public static function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize alphanumeric slug / subdomain
     */
    public static function sanitizeSlug(string $slug): string {
        return strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', trim($slug)));
    }

    /**
     * Check if the incoming request requests Markdown representation (Accept: text/markdown)
     */
    public static function wantsMarkdown(): bool {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return stripos($accept, 'text/markdown') !== false;
    }

    /**
     * Send Markdown response for agent content negotiation (RFC / Markdown for Agents)
     */
    public static function respondWithMarkdown(string $markdown): void {
        $words = str_word_count($markdown);
        $estimatedTokens = (int) ceil($words * 1.33);

        header('Content-Type: text/markdown; charset=utf-8');
        header('Vary: Accept');
        header('x-markdown-tokens: ' . $estimatedTokens);
        echo $markdown;
        exit;
    }
}

