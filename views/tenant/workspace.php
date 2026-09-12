<?php
$t = $currentTenant;
$stats = $tenantStats ?? ['plan' => $t['plan'] ?? 'starter', 'monthly_limit' => $t['monthly_limit'] ?? 2500, 'monthly_conversations' => 0, 'usage_percent' => 0];
$userPlan = strtolower($stats['plan'] ?? $t['plan'] ?? 'starter');
$planName = ucfirst($userPlan);
$isProOrEnterprise = in_array($userPlan, ['professional', 'enterprise']);
$isEnterprise = ($userPlan === 'enterprise');
$usagePercent = (float) ($stats['usage_percent'] ?? 0);
$monthlyConvs = (int) ($stats['monthly_conversations'] ?? 0);
$monthlyLimit = (int) ($stats['monthly_limit'] ?? 2500);
$barColor = $usagePercent > 90 ? '#ef4444' : ($usagePercent > 70 ? '#f59e0b' : '#10b981');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['business_name'] ?? 'Client'); ?> Workspace - ChatModel</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>
    <div class="ambient-glow"></div>

    <div class="login-card portal-layout">
        <!-- DEDICATED CLIENT USER WORKSPACE PORTAL -->
        <div class="dashboard-header">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap;">
                    <h2 style="color: var(--text-heading); font-size: 1.4rem; font-weight: 800;">
                        <?php echo htmlspecialchars($t['business_name']); ?>
                    </h2>
                    <span class="plan-badge <?php echo htmlspecialchars($stats['plan'] ?? 'starter'); ?>">
                        <?php echo $planName; ?> Plan
                    </span>
                </div>
                <div
                    style="font-size: 0.85rem; color: var(--text-body); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span>Workspace:</span>
                    <a href="/?subdomain=<?php echo htmlspecialchars($t['subdomain']); ?>" target="_blank"
                        class="tenant-domain-link" style="font-size: 0.85rem; font-weight: 600;">
                        https://<?php echo htmlspecialchars($t['subdomain']); ?>.chatmodel.in ↗
                    </a>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="/?subdomain=<?php echo htmlspecialchars($t['subdomain']); ?>" target="_blank" class="btn-logs"
                    style="text-decoration: none; padding: 7px 14px; font-size: 0.82rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px;">
                    Test Live Assistant
                </a>
                <button id="adminThemeBtn" class="theme-switch-btn" title="Toggle Day / Night Mode">
                    <span id="adminThemeIcon">☀️</span>
                </button>
                <a href="/login.php?logout=1"
                    style="color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Log out</a>
            </div>
        </div>

        <!-- Quick KPI Summary Cards -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <!-- Conversational Quota Card -->
            <div
                style="background: var(--form-box-bg); border: 1px solid var(--input-border); border-radius: 16px; padding: 18px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 0.8rem; font-weight: 700; color: var(--text-body); text-transform: uppercase; letter-spacing: 0.5px;">Monthly
                        Quota</span>
                    <span
                        style="font-size: 0.8rem; font-weight: 700; color: <?php echo $barColor; ?>;"><?php echo $usagePercent; ?>%
                        Used</span>
                </div>
                <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-heading); margin-bottom: 8px;">
                    <?php echo number_format($monthlyConvs); ?> <span
                        style="font-size: 0.85rem; font-weight: 500; color: var(--text-body);">/
                        <?php echo number_format($monthlyLimit); ?> convs</span>
                </div>
                <div
                    style="background: rgba(255,255,255,0.08); border-radius: 8px; height: 7px; overflow: hidden; width: 100%;">
                    <div
                        style="background: <?php echo $barColor; ?>; width: <?php echo min(100, $usagePercent); ?>%; height: 100%; border-radius: 8px; transition: width 0.4s ease;">
                    </div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-body); margin-top: 6px;">
                    <?php echo max(0, $monthlyLimit - $monthlyConvs); ?> conversations remaining this month
                </div>
            </div>

            <!-- Assistant Status Card -->
            <div
                style="background: var(--form-box-bg); border: 1px solid var(--input-border); border-radius: 16px; padding: 18px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span
                        style="font-size: 0.8rem; font-weight: 700; color: var(--text-body); text-transform: uppercase; letter-spacing: 0.5px;">Assistant
                        Status</span>
                    <span
                        style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; font-weight: 700; color: <?php echo $t['is_active'] ? '#10b981' : '#ef4444'; ?>;">
                        <span
                            style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo $t['is_active'] ? '#10b981' : '#ef4444'; ?>;"></span>
                        <?php echo $t['is_active'] ? 'Live & Active' : 'Paused'; ?>
                    </span>
                </div>
                <div
                    style="font-size: 1.1rem; font-weight: 700; color: var(--text-heading); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>AI Pipeline:</span>
                    <span style="font-size: 0.85rem; color: #10b981; font-weight: 600;">✓ Connected</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-body); line-height: 1.4;">
                    Theme Accent: <span
                        style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: <?php echo htmlspecialchars($t['theme_color']); ?>; vertical-align: middle; margin-left: 2px;"></span>
                    <?php echo htmlspecialchars($t['theme_color']); ?>
                </div>
            </div>

            <!-- Security & Access Card -->
            <div
                style="background: var(--form-box-bg); border: 1px solid var(--input-border); border-radius: 16px; padding: 18px 20px;">
                <div
                    style="font-size: 0.8rem; font-weight: 700; color: var(--text-body); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    Security & Chat Access
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.82rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--text-body);">Access Mode:</span>
                        <span
                            style="font-weight: 600; color: <?php echo ($isProOrEnterprise && ($t['chat_access_mode'] ?? 'public') === 'private') ? '#f59e0b' : '#10b981'; ?>;">
                            <?php
                            if (!$isProOrEnterprise) {
                                echo '🌐 Public (Starter Plan)';
                            } else {
                                echo ($t['chat_access_mode'] ?? 'public') === 'private' ? '🔒 Private (Login Req.)' : '🌐 Public (Open)';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workspace Settings Form -->
        <div
            style="background: var(--form-box-bg); padding: 22px 24px; border-radius: 18px; border: 1px solid var(--input-border); margin-bottom: 28px;">
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--header-border); padding-bottom: 12px;">
                <h3 style="color: var(--text-heading); font-size: 1.1rem; font-weight: 700;">⚙️ Assistant Configuration
                </h3>
                <span style="font-size: 0.8rem; color: var(--text-body);">Subdomain:
                    <code><?php echo htmlspecialchars($t['subdomain']); ?></code></span>
            </div>

            <form id="userWorkspaceForm" onsubmit="handleSaveUserWorkspace(event)">
                <input type="hidden" id="userSubdomain" value="<?php echo htmlspecialchars($t['subdomain']); ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label>Business / Brand Name</label>
                        <input type="text" id="userBusinessName" class="form-control"
                            value="<?php echo htmlspecialchars($t['business_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Brand Theme Color</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="color" id="userThemeColor" class="form-control"
                                value="<?php echo htmlspecialchars($t['theme_color'] ?? '#4f46e5'); ?>"
                                style="height: 42px; width: 60px; padding: 3px; cursor: pointer;"
                                oninput="document.getElementById('userThemeColorHex').value = this.value">
                            <input type="text" id="userThemeColorHex" class="form-control"
                                value="<?php echo htmlspecialchars($t['theme_color'] ?? '#4f46e5'); ?>"
                                style="font-family: 'JetBrains Mono', monospace;"
                                oninput="document.getElementById('userThemeColor').value = this.value">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Welcome Greeting Message</label>
                    <textarea id="userWelcomeMessage" class="form-control" rows="2"
                        required><?php echo htmlspecialchars($t['welcome_message'] ?? ''); ?></textarea>
                </div>

                <?php if ($isProOrEnterprise): ?>
                    <!-- Chat Access & Privacy Control (Public / Private) - Pro / Enterprise Feature -->
                    <div
                        style="background: var(--toggle-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 16px; margin-bottom: 16px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 0.88rem; font-weight: 700; color: var(--text-heading);">🔒 Chat Access &
                                Privacy Control</span>
                            <span class="plan-badge <?php echo htmlspecialchars($userPlan); ?>"
                                style="font-size: 0.68rem;"><?php echo ucfirst($userPlan); ?> Feature</span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-body); margin-bottom: 12px; line-height: 1.4;">
                            Choose whether your AI assistant is open to the public or locked strictly for internal team use.
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 0.78rem;">Chat Access Mode</label>
                            <select id="userChatAccessMode" class="form-control" onchange="syncPasscodeRequirement()">
                                <option value="public" <?php echo ($t['chat_access_mode'] ?? 'public') === 'public' ? 'selected' : ''; ?>>🌐 Public — Anyone visiting your assistant URL can chat freely (No
                                    login required)</option>
                                <option value="private" <?php echo ($t['chat_access_mode'] ?? '') === 'private' ? 'selected' : ''; ?>>🔒 Private / Internal Only — Authentication strictly required to chat (Company
                                    internal use)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label
                                style="font-size: 0.78rem; display: flex; justify-content: space-between; align-items: center;">
                                <span>Dedicated Internal Team Passcode / Key</span>
                                <span id="passcodeRequiredBadge"
                                    style="color: #ef4444; font-weight: 700; font-size: 0.72rem; <?php echo ($t['chat_access_mode'] ?? '') === 'private' ? '' : 'display: none;'; ?>">*
                                    Mandatory for Private</span>
                            </label>
                            <input type="text" id="userInternalAccessKey" class="form-control"
                                value="<?php echo htmlspecialchars($t['internal_access_key'] ?? ''); ?>"
                                placeholder="e.g. team_secret_2026 (Mandatory for Private mode)" <?php echo ($t['chat_access_mode'] ?? '') === 'private' ? 'required' : ''; ?>>
                            <div style="font-size: 0.72rem; color: var(--text-body); margin-top: 4px;">
                                Staff can log in using this passcode OR your workspace password when visiting
                                <code>https://<?php echo htmlspecialchars($t['subdomain']); ?>.chatmodel.in</code>.
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Starter Plan: Hidden default public mode -->
                    <input type="hidden" id="userChatAccessMode" value="public">
                    <input type="hidden" id="userInternalAccessKey" value="">
                <?php endif; ?>

                <?php if ($isEnterprise): ?>
                    <!-- Dedicated AI Automation Webhook Endpoint (Enterprise Exclusive - Read-Only & Copyable) -->
                    <div
                        style="background: var(--toggle-bg); border: 1px solid var(--input-border); border-radius: 14px; padding: 16px; margin-bottom: 16px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 0.88rem; font-weight: 700; color: var(--text-heading);">⚡ Dedicated AI Automation Webhook</span>
                            <span class="plan-badge enterprise" style="font-size: 0.68rem;">Enterprise Exclusive</span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-body); margin-bottom: 10px; line-height: 1.4;">
                            Your assigned webhook endpoint for n8n, Make, Zapier, or custom AI infrastructure. This endpoint is managed by the platform administrator.
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="text" id="enterpriseWebhookUrl" class="form-control"
                                    value="<?php echo htmlspecialchars($t['webhook_url'] ?? ''); ?>"
                                    readonly
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 0.82rem; background: rgba(0,0,0,0.25); cursor: default; user-select: all; color: #38bdf8;"
                                    onclick="this.select()">
                                <button type="button" onclick="copyEnterpriseWebhookUrl()" class="btn-logs"
                                    style="padding: 10px 16px; font-size: 0.82rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    📋 Copy URL
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-submit" style="padding: 14px; font-size: 1rem;">
                    Save Workspace Settings
                </button>
            </form>
        </div>

        <!-- Security & Password Change Card -->
        <div
            style="background: var(--form-box-bg); padding: 20px 24px; border-radius: 18px; border: 1px solid var(--input-border); margin-bottom: 28px;">
            <h4 style="color: var(--text-heading); font-size: 0.98rem; font-weight: 700; margin-bottom: 12px;">🔒
                Security & Client Portal Password</h4>
            <form onsubmit="handleUpdateUserPassword(event)"
                style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Set New Password (min 6 characters)</label>
                    <input type="password" id="userNewPassword" class="form-control" placeholder="••••••••" required
                        minlength="6">
                </div>
                <button type="submit" class="btn-edit" style="height: 42px; padding: 0 20px; font-size: 0.88rem;">
                    Update Password
                </button>
            </form>
        </div>

        <?php if ($isEnterprise): ?>
            <!-- Live Conversation History & Chat Logs (Enterprise Exclusive) -->
            <div
                style="background: var(--form-box-bg); padding: 22px 24px; border-radius: 18px; border: 1px solid var(--input-border);">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 2px;">
                            <h3 style="color: var(--text-heading); font-size: 1.1rem; font-weight: 700;">💬 Recent Customer Conversations & Chat Logs</h3>
                            <span class="plan-badge enterprise" style="font-size: 0.68rem;">Enterprise</span>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--text-body);">Inspecting live incoming messages captured by your assistant</div>
                    </div>
                    <button type="button" onclick="refreshUserChatLogs()" class="btn-logs"
                        style="padding: 6px 14px; font-size: 0.8rem; display: flex; align-items: center; gap: 5px;">
                        🔄 Refresh Logs
                    </button>
                </div>

                <div id="userLogsContainer" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($tenantLogs)): ?>
                        <div style="text-align: center; padding: 36px 20px; color: var(--text-body); font-size: 0.9rem;">
                            No chat conversations recorded yet. When users talk to your assistant at
                            <code>/?subdomain=<?php echo htmlspecialchars($t['subdomain']); ?></code>, transcripts will appear
                            here in real-time.
                        </div>
                    <?php else: ?>
                        <table class="tenant-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Sender</th>
                                    <th>Message Content</th>
                                    <th>Session ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tenantLogs as $log):
                                    $sender = strtolower($log['sender'] ?? 'user');
                                    $isUserSender = in_array($sender, ['user', 'customer', 'client']);
                                    ?>
                                    <tr>
                                        <td style="font-size: 0.76rem; color: var(--text-body); white-space: nowrap;">
                                            <?php echo htmlspecialchars($log['created_at']); ?>
                                        </td>
                                        <td>
                                            <span
                                                style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: <?php echo $isUserSender ? 'rgba(99, 102, 241, 0.15)' : 'rgba(16, 185, 129, 0.15)'; ?>; color: <?php echo $isUserSender ? '#818cf8' : '#10b981'; ?>;">
                                                <?php echo $isUserSender ? '👤 Customer' : '🤖 Assistant'; ?>
                                            </span>
                                        </td>
                                        <td
                                            style="font-size: 0.85rem; color: var(--text-heading); max-width: 380px; word-break: break-word;">
                                            <?php echo htmlspecialchars($log['message'] ?? ''); ?>
                                        </td>
                                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.74rem; color: #0284c7;">
                                            <?php echo htmlspecialchars(substr($log['session_id'], 0, 14)); ?>...
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="/assets/js/workspace.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>

</html>