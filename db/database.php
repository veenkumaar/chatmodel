<?php
/**
 * Database Management & Admin Authentication for ChatModel Multi-Tenant SaaS
 */
require_once __DIR__ . '/security.php';

class Database
{
    private static ?PDO $pdo = null;
    private static string $dbFile = __DIR__ . '/../data/tenants.db';

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dataDir = dirname(self::$dbFile);
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0777, true);
            }

            self::$pdo = new PDO('sqlite:' . self::$dbFile);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Production SQLite optimizations for high concurrent traffic
            self::$pdo->exec("PRAGMA journal_mode = WAL;");
            self::$pdo->exec("PRAGMA busy_timeout = 5000;");
            self::$pdo->exec("PRAGMA synchronous = NORMAL;");

            // Initialize schema if not exists
            self::initSchema();
        }
        return self::$pdo;
    }

    private static function initSchema(): void
    {
        $db = self::$pdo;
        $db->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS tenants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                subdomain TEXT NOT NULL UNIQUE,
                password_hash TEXT DEFAULT '',
                business_name TEXT NOT NULL,
                webhook_url TEXT NOT NULL,
                welcome_message TEXT DEFAULT 'Hello! How can we help your business today?',
                theme_color TEXT DEFAULT '#4f46e5',
                plan TEXT DEFAULT 'starter',
                monthly_limit INTEGER DEFAULT 2500,
                chat_access_mode TEXT DEFAULT 'public',
                internal_access_key TEXT DEFAULT '',
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS chat_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                subdomain TEXT NOT NULL,
                session_id TEXT NOT NULL,
                sender TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS inquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                phone TEXT DEFAULT '',
                company TEXT DEFAULT '',
                plan TEXT DEFAULT 'professional',
                message TEXT NOT NULL,
                ip_address TEXT DEFAULT '',
                status TEXT DEFAULT 'new',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Seed default admin user if none exists
        $adminStmt = $db->query("SELECT COUNT(*) as cnt FROM admins");
        $adminCount = $adminStmt->fetch()['cnt'] ?? 0;
        if ($adminCount == 0) {
            self::createAdmin('admin', 'admin123');
        }

        // Seed default tenants if table is empty
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM tenants");
        $count = $stmt->fetch()['cnt'] ?? 0;
        if ($count == 0) {
            // 1. Starter Plan Tenant (Public mode default)
            self::createTenant(
                'demo',
                'ChatModel Demo Store',
                'https://api.chatmodel.in/webhook/chatmodel',
                'Hi! Welcome to ChatModel Demo Store. How may I assist your business today?',
                '#6366f1',
                'starter',
                2500,
                1,
                'public',
                ''
            );

            // 2. Professional Plan Tenant (Configurable Public/Private)
            self::createTenant(
                'aditya',
                'Aditya Photography',
                'https://api.chatmodel.in/webhook/adityaansul',
                'Welcome to Aditya Photography! Ask me anything about our photography packages, bookings, and portfolios.',
                '#0284c7',
                'professional',
                15000,
                1,
                'public',
                'aditya-team-2026'
            );

            // 3. Enterprise Plan Tenant (Dedicated Webhook, Logs & Private Passcode)
            self::createTenant(
                'enterprise',
                'Apex Enterprise Global Corp',
                'https://api.chatmodel.in/webhook/chat-enterprise-apex',
                'Welcome to Apex Enterprise Global Concierge. Secure confidential communications channel active.',
                '#a855f7',
                'enterprise',
                -1,
                1,
                'private',
                'apex-secret-2026'
            );

            // Seed demo chat logs for enterprise & aditya
            self::logMessage('enterprise', 'sess_corp_001', 'user', 'Hello, we would like to initiate custom ERP sync.');
            self::logMessage('enterprise', 'sess_corp_001', 'bot', 'Greetings! Your dedicated Enterprise AI pipeline is online. ERP data bridges are synchronized.');
            self::logMessage('aditya', 'sess_aditya_101', 'user', 'Can I track shipment CM-8921?');
            self::logMessage('aditya', 'sess_aditya_101', 'bot', 'Shipment CM-8921 is in transit and estimated for delivery by 5:00 PM today.');

            // Seed a sample client inquiry
            self::createInquiry(
                'Rajesh Sharma',
                'rajesh@apexventures.com',
                '+91 98765 43210',
                'Apex Ventures',
                'enterprise',
                'Interested in deploying 5 dedicated subdomain workspaces for our subsidiary companies.'
            );
        }
    }

    public static function createAdmin(string $username, string $password): bool
    {
        $db = self::getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT OR REPLACE INTO admins (username, password_hash) VALUES (:username, :password_hash)");
        return $stmt->execute([
            ':username' => trim($username),
            ':password_hash' => $hash
        ]);
    }

    public static function updateAdminPassword(string $username, string $newPassword): bool
    {
        $db = self::getConnection();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admins SET password_hash = :hash WHERE username = :username");
        return $stmt->execute([
            ':hash' => $hash,
            ':username' => trim($username)
        ]);
    }

    public static function verifyAdmin(string $username, string $password): bool
    {
        $db = self::getConnection();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => trim($username)]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            return true;
        }
        return false;
    }

    public static function verifyTenantUser(string $login, string $password): ?array
    {
        $login = strtolower(trim($login));
        $db = self::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE LOWER(subdomain) = :sub LIMIT 1");
        $stmt->execute([':sub' => $login]);
        $tenant = $stmt->fetch();

        if ($tenant) {
            // If password_hash is empty, default password is "client123" or subdomain name
            if (empty($tenant['password_hash'])) {
                if ($password === 'client123' || strtolower($password) === strtolower($tenant['subdomain'])) {
                    return $tenant;
                }
            } else {
                if (password_verify($password, $tenant['password_hash'])) {
                    return $tenant;
                }
            }
        }
        return null;
    }

    public static function updateTenantPassword(string $subdomain, string $newPassword): bool
    {
        $db = self::getConnection();
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE tenants SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE LOWER(subdomain) = :sub");
        return $stmt->execute([
            ':hash' => $hash,
            ':sub' => strtolower(trim($subdomain))
        ]);
    }

    public static function getAllTenants(): array
    {
        $db = self::getConnection();
        $stmt = $db->query("SELECT * FROM tenants ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function getTenantConversationStats(string $subdomain): array
    {
        $db = self::getConnection();
        $subdomain = strtolower(trim($subdomain));

        // Get monthly unique conversations (distinct session_ids active this month)
        $monthStmt = $db->prepare("
            SELECT COUNT(DISTINCT session_id) as monthly_conversations,
                   COUNT(*) as total_messages
            FROM chat_logs 
            WHERE LOWER(subdomain) = :subdomain 
              AND created_at >= date('now', 'start of month')
        ");
        $monthStmt->execute([':subdomain' => $subdomain]);
        $stats = $monthStmt->fetch() ?: ['monthly_conversations' => 0, 'total_messages' => 0];

        // Total all-time messages
        $allStmt = $db->prepare("SELECT COUNT(*) as all_time_messages, COUNT(DISTINCT session_id) as all_time_conversations FROM chat_logs WHERE LOWER(subdomain) = :subdomain");
        $allStmt->execute([':subdomain' => $subdomain]);
        $allStats = $allStmt->fetch() ?: ['all_time_messages' => 0, 'all_time_conversations' => 0];

        $tenant = self::getTenantBySubdomain($subdomain);
        $plan = $tenant['plan'] ?? 'starter';
        $monthlyLimit = isset($tenant['monthly_limit']) ? (int) $tenant['monthly_limit'] : 2500;
        if ($plan === 'enterprise' || $monthlyLimit < 0) {
            $monthlyLimit = -1; // Unlimited
        }

        $monthlyConvs = (int) $stats['monthly_conversations'];
        $usagePercent = ($monthlyLimit > 0) ? min(100, round(($monthlyConvs / $monthlyLimit) * 100, 1)) : 0;
        $isOverQuota = ($monthlyLimit > 0) && ($monthlyConvs >= $monthlyLimit);

        return [
            'plan' => $plan,
            'monthly_limit' => $monthlyLimit,
            'monthly_conversations' => $monthlyConvs,
            'monthly_messages' => (int) $stats['total_messages'],
            'all_time_conversations' => (int) $allStats['all_time_conversations'],
            'all_time_messages' => (int) $allStats['all_time_messages'],
            'usage_percent' => $usagePercent,
            'is_over_quota' => $isOverQuota
        ];
    }

    public static function getAllTenantsWithStats(): array
    {
        $tenants = self::getAllTenants();
        foreach ($tenants as &$tenant) {
            $stats = self::getTenantConversationStats($tenant['subdomain']);
            $tenant['stats'] = $stats;
        }
        return $tenants;
    }

    public static function getTenantBySubdomain(string $subdomain): ?array
    {
        $subdomain = strtolower(trim($subdomain));
        $db = self::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE LOWER(subdomain) = :subdomain LIMIT 1");
        $stmt->execute([':subdomain' => $subdomain]);
        $result = $stmt->fetch();
        return $result ? $result : null;
    }

    public static function isSubdomainAvailable(string $subdomain): array
    {
        $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', trim($subdomain)));

        if (strlen($subdomain) < 3) {
            return ['available' => false, 'subdomain' => $subdomain, 'reason' => 'Subdomain must be at least 3 characters.'];
        }
        if (strlen($subdomain) > 63) {
            return ['available' => false, 'subdomain' => $subdomain, 'reason' => 'Subdomain cannot exceed 63 characters.'];
        }

        $reserved = ['www', 'admin', 'api', 'login', 'n8n', 'app', 'mail', 'dashboard', 'status', 'auth', 'cname', 'root', 'static', 'assets', 'cdn', 'demo', 'chatmodel'];
        if (in_array($subdomain, $reserved)) {
            return ['available' => false, 'subdomain' => $subdomain, 'reason' => 'This is a reserved system subdomain and cannot be registered.'];
        }

        $existing = self::getTenantBySubdomain($subdomain);
        if ($existing) {
            return ['available' => false, 'subdomain' => $subdomain, 'reason' => "Subdomain '{$subdomain}' is already taken."];
        }

        return [
            'available' => true,
            'subdomain' => $subdomain,
            'full_domain' => "{$subdomain}.chatmodel.in",
            'preview_url' => "http://localhost:8000/?subdomain={$subdomain}",
            'reason' => "Subdomain '{$subdomain}.chatmodel.in' is available!"
        ];
    }

    public static function getTenantChatHistory(string $subdomain, int $limit = 50): array
    {
        $db = self::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM chat_logs 
            WHERE LOWER(subdomain) = :subdomain 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':subdomain', strtolower(trim($subdomain)), PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function checkQuota(string $subdomain): bool
    {
        $stats = self::getTenantConversationStats($subdomain);
        return !$stats['is_over_quota'];
    }

    public static function verifyChatAccess(string $subdomain, string $passcodeOrPassword): bool
    {
        $subdomain = strtolower(trim($subdomain));
        $passcodeOrPassword = trim($passcodeOrPassword);
        if (empty($passcodeOrPassword))
            return false;

        $tenant = self::getTenantBySubdomain($subdomain);
        if (!$tenant)
            return false;

        // 1. Check dedicated internal access key if configured
        if (!empty($tenant['internal_access_key']) && hash_equals($tenant['internal_access_key'], $passcodeOrPassword)) {
            return true;
        }

        // 2. Check tenant password_hash or default password
        if (empty($tenant['password_hash'])) {
            if ($passcodeOrPassword === 'client123' || strtolower($passcodeOrPassword) === strtolower($tenant['subdomain'])) {
                return true;
            }
        } else {
            if (password_verify($passcodeOrPassword, $tenant['password_hash'])) {
                return true;
            }
        }

        return false;
    }

    public static function createTenant(
        string $subdomain,
        string $businessName,
        string $webhookUrl,
        string $welcomeMessage = 'Hello! How can we help your business today?',
        string $themeColor = '#4f46e5',
        string $plan = 'starter',
        int $monthlyLimit = 2500,
        int $isActive = 1,
        string $chatAccessMode = 'public',
        string $internalAccessKey = ''
    ): bool {
        $db = self::getConnection();
        $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', trim($subdomain)));
        if (empty($subdomain))
            return false;

        // Auto-assign limit based on plan if standard
        if ($plan === 'professional' && $monthlyLimit == 2500)
            $monthlyLimit = 15000;
        if ($plan === 'enterprise')
            $monthlyLimit = -1;

        $validModes = ['public', 'private'];
        if (!in_array($chatAccessMode, $validModes)) {
            $chatAccessMode = 'public';
        }

        $stmt = $db->prepare("
            INSERT INTO tenants (subdomain, business_name, webhook_url, welcome_message, theme_color, plan, monthly_limit, chat_access_mode, internal_access_key, is_active)
            VALUES (:subdomain, :business_name, :webhook_url, :welcome_message, :theme_color, :plan, :monthly_limit, :chat_access_mode, :internal_access_key, :is_active)
        ");
        return $stmt->execute([
            ':subdomain' => $subdomain,
            ':business_name' => $businessName,
            ':webhook_url' => $webhookUrl,
            ':welcome_message' => $welcomeMessage,
            ':theme_color' => $themeColor,
            ':plan' => $plan,
            ':monthly_limit' => $monthlyLimit,
            ':chat_access_mode' => $chatAccessMode,
            ':internal_access_key' => trim($internalAccessKey),
            ':is_active' => $isActive
        ]);
    }

    public static function updateTenantStatus(string $subdomain, int $isActive): bool
    {
        $db = self::getConnection();
        $stmt = $db->prepare("UPDATE tenants SET is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE subdomain = :subdomain");
        return $stmt->execute([
            ':is_active' => $isActive ? 1 : 0,
            ':subdomain' => strtolower(trim($subdomain))
        ]);
    }

    public static function updateTenant(
        string $subdomain,
        string $businessName,
        string $webhookUrl,
        string $welcomeMessage,
        string $themeColor,
        string $plan = 'starter',
        int $monthlyLimit = 2500,
        int $isActive = 1,
        string $chatAccessMode = 'public',
        string $internalAccessKey = ''
    ): bool {
        $db = self::getConnection();
        if ($plan === 'professional' && $monthlyLimit == 2500)
            $monthlyLimit = 15000;
        if ($plan === 'enterprise')
            $monthlyLimit = -1;

        $validModes = ['public', 'private'];
        if (!in_array($chatAccessMode, $validModes)) {
            $chatAccessMode = 'public';
        }

        $stmt = $db->prepare("
            UPDATE tenants 
            SET business_name = :business_name,
                webhook_url = :webhook_url,
                welcome_message = :welcome_message,
                theme_color = :theme_color,
                plan = :plan,
                monthly_limit = :monthly_limit,
                chat_access_mode = :chat_access_mode,
                internal_access_key = :internal_access_key,
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE subdomain = :subdomain
        ");
        return $stmt->execute([
            ':business_name' => $businessName,
            ':webhook_url' => $webhookUrl,
            ':welcome_message' => $welcomeMessage,
            ':theme_color' => $themeColor,
            ':plan' => $plan,
            ':monthly_limit' => $monthlyLimit,
            ':chat_access_mode' => $chatAccessMode,
            ':internal_access_key' => trim($internalAccessKey),
            ':is_active' => $isActive ? 1 : 0,
            ':subdomain' => strtolower(trim($subdomain))
        ]);
    }

    public static function deleteTenant(string $subdomain): bool
    {
        $db = self::getConnection();
        $sub = strtolower(trim($subdomain));
        $stmt = $db->prepare("DELETE FROM tenants WHERE subdomain = :subdomain");
        $stmt->execute([':subdomain' => $sub]);
        // Also cleanup chat logs for deleted tenant
        $logStmt = $db->prepare("DELETE FROM chat_logs WHERE LOWER(subdomain) = :subdomain");
        $logStmt->execute([':subdomain' => $sub]);
        return true;
    }

    public static function logMessage(string $subdomain, string $sessionId, string $sender, string $message): void
    {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare("
                INSERT INTO chat_logs (subdomain, session_id, sender, message)
                VALUES (:subdomain, :session_id, :sender, :message)
            ");
            $stmt->execute([
                ':subdomain' => strtolower(trim($subdomain)),
                ':session_id' => $sessionId,
                ':sender' => $sender,
                ':message' => $message
            ]);
        } catch (Exception $e) {
            // Non-blocking log failure
        }
    }

    public static function createInquiry(
        string $name,
        string $email,
        string $phone = '',
        string $company = '',
        string $plan = 'professional',
        string $message = '',
        string $ip = ''
    ): bool {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare("
                INSERT INTO inquiries (name, email, phone, company, plan, message, ip_address, status)
                VALUES (:name, :email, :phone, :company, :plan, :message, :ip, 'new')
            ");
            return $stmt->execute([
                ':name' => trim($name),
                ':email' => strtolower(trim($email)),
                ':phone' => trim($phone),
                ':company' => trim($company),
                ':plan' => trim($plan),
                ':message' => trim($message),
                ':ip' => trim($ip)
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getAllInquiries(): array
    {
        try {
            $db = self::getConnection();
            $stmt = $db->query("SELECT * FROM inquiries ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public static function getNewInquiriesCount(): int
    {
        try {
            $db = self::getConnection();
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM inquiries WHERE status = 'new'");
            $res = $stmt->fetch();
            return (int) ($res['cnt'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function updateInquiryStatus(int $id, string $status): bool
    {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare("UPDATE inquiries SET status = :status WHERE id = :id");
            return $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function deleteInquiry(int $id): bool
    {
        try {
            $db = self::getConnection();
            $stmt = $db->prepare("DELETE FROM inquiries WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
