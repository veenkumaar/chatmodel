<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - ChatModel SaaS</title>
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

    <!-- Edit Tenant Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">⚙️ Configure Dedicated Workspace</div>
                <button class="modal-close" onclick="closeEditModal()">✕</button>
            </div>
            <form id="editTenantForm" onsubmit="handleSaveEdit(event)">
                <input type="hidden" id="editSubdomain">

                <div class="form-group">
                    <label>Subdomain Endpoint</label>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; color: #0284c7; padding: 6px 0;" id="editSubdomainLabel"></div>
                </div>

                <div class="form-group">
                    <label>Business / Client Brand Name</label>
                    <input type="text" id="editBusinessName" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>AI Automation Webhook Endpoint</label>
                    <input type="url" id="editWebhookUrl" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Welcome Greeting Message</label>
                    <textarea id="editWelcomeMessage" class="form-control" rows="2" required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Brand Theme Color</label>
                        <input type="color" id="editThemeColor" class="form-control" style="height: 42px; padding: 3px; cursor: pointer;">
                    </div>
                    <div class="form-group">
                        <label>Subscription Plan Tier</label>
                        <select id="editPlan" class="form-control">
                            <option value="starter">Starter (2,500/mo)</option>
                            <option value="professional">Professional (15,000/mo)</option>
                            <option value="enterprise">Enterprise (Unlimited)</option>
                        </select>
                    </div>
                </div>

                <div style="background: var(--toggle-bg); border: 1px solid var(--input-border); border-radius: 12px; padding: 12px; margin-bottom: 16px;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-heading); margin-bottom: 8px;">🔒 Chat Access & Privacy Control</div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 0.75rem;">Access Mode</label>
                        <select id="editChatAccessMode" class="form-control">
                            <option value="public">🌐 Public (Open to anyone)</option>
                            <option value="private">🔒 Private / Internal (Login required)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.75rem;">Internal Access Passcode / Key (Optional)</label>
                        <input type="text" id="editInternalAccessKey" class="form-control" placeholder="e.g. team_secret_key (or uses workspace password)">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeEditModal()" style="flex: 1; background: var(--toggle-bg); color: var(--text-heading); border: 1px solid var(--input-border); padding: 10px; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-submit" style="flex: 1; margin-top: 0;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversation Logs Modal -->
    <div id="logsModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 680px;">
            <div class="modal-header">
                <div class="modal-title">💬 Live Session History: <span id="logsSubdomainDisplay" style="color: #0284c7;"></span></div>
                <button class="modal-close" onclick="closeLogsModal()">✕</button>
            </div>
            <div id="logsStatsBar" style="display: flex; gap: 16px; margin-bottom: 14px; font-size: 0.8rem; background: var(--toggle-bg); padding: 10px 14px; border-radius: 10px;"></div>
            <div id="logsContainer" style="max-height: 380px; overflow-y: auto; padding-right: 4px;"></div>
        </div>
    </div>

    <!-- Change Admin Password Modal -->
    <div id="adminPasswordModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px;">
            <div class="modal-header">
                <div class="modal-title">🔑 Change Admin Password</div>
                <button class="modal-close" onclick="closeAdminPasswordModal()">✕</button>
            </div>
            <form id="adminPasswordForm" onsubmit="handleAdminPasswordChange(event)">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="adminCurrentPassword" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label>New Password (min 6 characters)</label>
                    <input type="password" id="adminNewPassword" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="adminConfirmPassword" class="form-control" placeholder="••••••••" required minlength="6" autocomplete="new-password">
                </div>
                <div id="adminPasswordStatus" style="font-size: 0.78rem; display: none; line-height: 1.4; padding: 8px 12px; border-radius: 8px; margin-bottom: 12px;"></div>
                <div style="display: flex; gap: 10px; margin-top: 18px;">
                    <button type="button" onclick="closeAdminPasswordModal()" style="flex: 1; background: var(--toggle-bg); color: var(--text-heading); border: 1px solid var(--input-border); padding: 10px; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-submit" style="flex: 1; margin-top: 0;">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Subdomain Workspace Password Modal (Admin) -->
    <div id="resetSubdomainPasswordModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px;">
            <div class="modal-header">
                <div class="modal-title">🔑 Reset Subdomain Password</div>
                <button class="modal-close" onclick="closeResetSubdomainPasswordModal()">✕</button>
            </div>
            <form id="resetSubdomainPasswordForm" onsubmit="handleResetSubdomainPassword(event)">
                <input type="hidden" id="resetTargetSubdomain">
                <div class="form-group">
                    <label>Workspace Subdomain</label>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.95rem; font-weight: 700; color: #0284c7; padding: 4px 0 10px 0;" id="resetSubdomainDisplay"></div>
                </div>
                <div class="form-group">
                    <label>New Workspace Password (min 6 chars)</label>
                    <input type="password" id="resetNewPassword" class="form-control" placeholder="Enter new password" required minlength="6" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="resetConfirmPassword" class="form-control" placeholder="Re-type new password" required minlength="6" autocomplete="new-password">
                </div>
                <div id="resetPasswordStatus" style="font-size: 0.78rem; display: none; line-height: 1.4; padding: 8px 12px; border-radius: 8px; margin-bottom: 12px;"></div>
                <div style="display: flex; gap: 10px; margin-top: 18px;">
                    <button type="button" onclick="closeResetSubdomainPasswordModal()" style="flex: 1; background: var(--toggle-bg); color: var(--text-heading); border: 1px solid var(--input-border); padding: 10px; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                    <button type="submit" class="btn-submit" style="flex: 1; margin-top: 0;">Set Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Tenant Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px; text-align: center;">
            <input type="hidden" id="deleteTargetSubdomain">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">⚠️</div>
            <div class="modal-title" style="margin-bottom: 8px;">Delete Workspace?</div>
            <p style="color: var(--text-body); font-size: 0.88rem; line-height: 1.5; margin-bottom: 20px;">
                Are you sure you want to permanently remove <strong id="deleteSubdomainDisplay" style="color: #ef4444;"></strong>? This action will disable the assistant and cannot be undone.
            </p>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="closeDeleteModal()" style="flex: 1; background: var(--toggle-bg); color: var(--text-heading); border: 1px solid var(--input-border); padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="button" onclick="executeDeleteTenant()" style="flex: 1; background: #ef4444; color: #fff; padding: 12px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">Yes, Delete</button>
            </div>
        </div>
    </div>

    <div class="login-card portal-layout">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div>
                <h2 style="color: var(--text-heading); font-size: 1.35rem; font-weight: 700; margin-bottom: 2px;">
                    Global Admin & Subdomain Control</h2>
                <div style="font-size: 0.85rem; color: var(--text-body);">Manage all tenant webhook pipelines, conversation quotas, and service states.</div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span class="admin-tag">🛡️ <?php echo htmlspecialchars($adminUser); ?></span>
                <button type="button" onclick="openAdminPasswordModal()" class="btn-logs" style="padding: 5px 10px; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px;">
                    🔑 Change Password
                </button>
                <button id="adminThemeBtn" class="theme-switch-btn" title="Toggle Day / Night Mode">
                    <span id="adminThemeIcon">☀️</span>
                </button>
                <a href="/" style="color: var(--text-body); text-decoration: none; font-size: 0.85rem;">View Site</a>
                <a href="/login.php?logout=1" style="color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Log out</a>
            </div>
        </div>

        <!-- Admin Navigation Tabs -->
        <div class="admin-nav-tabs">
            <button type="button" class="admin-tab-btn active" id="tabBtnSubdomains" onclick="switchAdminTab('subdomains')">
                🌐 Dedicated Subdomains (<?php echo count($allTenants); ?>)
            </button>
            <button type="button" class="admin-tab-btn" id="tabBtnInquiries" onclick="switchAdminTab('inquiries')">
                📥 Client Inquiries & Leads
                <?php if ($newInquiriesCount > 0): ?>
                    <span class="tab-badge" id="newInquiryBadge"><?php echo $newInquiriesCount; ?> New</span>
                <?php endif; ?>
            </button>
        </div>

        <!-- TAB 1: SUBDOMAINS MANAGEMENT -->
        <div id="adminTabSubdomains" class="admin-tab-content active">
            <!-- Provision Form (Systematic Grid) -->
            <form id="createTenantForm" onsubmit="handleCreateTenant(event)" class="systematic-provision-form">
                <div class="form-header-bar">
                    <div class="form-header-title">
                        <span class="header-icon">🚀</span>
                        <div>
                            <h4>Provision Dedicated Subdomain & Workspace</h4>
                            <p>Deploy an isolated assistant workspace with custom routing, access controls, and quota tier</p>
                        </div>
                    </div>
                    <span class="instant-badge">⚡ Instant Provisioning</span>
                </div>

                <div class="systematic-grid">
                    <!-- Row 1: Workspace Core Identity -->
                    <div class="form-field">
                        <label>
                            <span>Dedicated Subdomain Slug</span>
                            <span id="subdomainCheckStatus" style="font-size: 0.74rem; font-weight: 600; display: none;"></span>
                        </label>
                        <div class="subdomain-input-box">
                            <span class="input-icon">🔗</span>
                            <input type="text" id="newSubdomain" placeholder="e.g. acme" required pattern="[a-zA-Z0-9-]+" title="Alphanumeric characters only" oninput="handleSubdomainInput(this.value)">
                            <span class="subdomain-badge">.chatmodel.in</span>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Business / Brand Name</label>
                        <div class="standard-input-box">
                            <span class="input-icon">🏢</span>
                            <input type="text" id="newBusinessName" placeholder="Acme Logistics & AI" required>
                        </div>
                    </div>

                    <!-- Row 2: AI Automation & Subscription Plan -->
                    <div class="form-field">
                        <label>AI Automation Webhook Endpoint</label>
                        <div class="standard-input-box">
                            <span class="input-icon">⚡</span>
                            <input type="url" id="newWebhookUrl" value="https://api.chatmodel.in/webhook/chat-acme" placeholder="https://api.chatmodel.in/webhook/..." required style="font-family: 'JetBrains Mono', monospace; font-size: 0.84rem;">
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Subscription Plan Tier & Quota</label>
                        <div class="standard-input-box">
                            <span class="input-icon">📊</span>
                            <select id="newPlan">
                                <option value="starter">Starter Plan (2,500 conv/mo)</option>
                                <option value="professional" selected>Professional Plan (15,000 conv/mo)</option>
                                <option value="enterprise">Agency & Enterprise (Unlimited)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Brand Theme Color -->
                    <div class="form-field">
                        <label>Brand Theme Color</label>
                        <div class="color-picker-box">
                            <span class="input-icon">🎨</span>
                            <input type="color" id="newThemeColor" value="#4f46e5" oninput="document.getElementById('newThemeColorHex').value = this.value">
                            <input type="text" id="newThemeColorHex" class="color-hex-text" value="#4f46e5">
                        </div>
                    </div>

                    <!-- Row 4: Chat Access & Security -->
                    <div class="form-field">
                        <label>
                            <span>Chat Access Mode</span>
                            <span class="optional-tag">Security</span>
                        </label>
                        <div class="standard-input-box">
                            <span class="input-icon">🔒</span>
                            <select id="newChatAccessMode">
                                <option value="public" selected>🌐 Public (Open to all visitors)</option>
                                <option value="private">🔒 Private / Internal (Login required)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-field" id="newInternalKeyField" style="display: none;">
                        <label>
                            <span>Internal Team Passcode / Key</span>
                            <span class="optional-tag" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">Mandatory for Private</span>
                        </label>
                        <div class="standard-input-box">
                            <span class="input-icon">🔑</span>
                            <input type="password" id="newInternalAccessKey" placeholder="e.g. acme-secret-2025" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <span class="security-note">🔒 Isolated container & dedicated automated SSL deployed instantly</span>
                    <button type="submit" class="btn-deploy-tenant">
                        <span>⚡ Provision Dedicated Workspace</span>
                        <span>➔</span>
                    </button>
                </div>
            </form>

            <!-- Search and Filter Bar -->
            <div class="search-and-filter-bar">
                <div class="search-input-wrapper">
                    <span class="search-field-icon">🔍</span>
                    <input type="text" id="searchInput" class="search-field-input" placeholder="Search tenant subdomains, brand names, or plans..." oninput="filterTenants()">
                </div>
                <div class="workspaces-count-pill">
                    <span>Workspaces</span>
                    <span class="count-number" id="tenantCount"><?php echo count($allTenants); ?></span>
                </div>
            </div>

            <!-- Tenants List Table -->
            <div class="table-responsive-wrapper">
                <table class="tenant-table">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Subdomain Workspace</th>
                            <th style="width: 30%;">Plan & Quota</th>
                            <th style="width: 16%; text-align: center;">Status (Kill-Switch)</th>
                            <th style="width: 22%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tenantTableBody">
                        <?php foreach ($allTenants as $t):
                            $sub = htmlspecialchars($t['subdomain']);
                            $isActive = (int) $t['is_active'] === 1;
                            $stats = $t['stats'] ?? Database::getTenantConversationStats($t['subdomain']);
                            $plan = $stats['plan'] ?? $t['plan'] ?? 'starter';
                            $isEnterprise = strtolower($plan) === 'enterprise';
                            $monthlyConvs = (int) ($stats['monthly_conversations'] ?? 0);
                            $monthlyLimit = (int) ($stats['monthly_limit'] ?? $t['monthly_limit'] ?? 2500);
                            $usagePercent = (float) ($stats['usage_percent'] ?? 0);
                            $barColor = $usagePercent > 90 ? '#ef4444' : ($usagePercent > 70 ? '#f59e0b' : '#10b981');
                            $accessMode = $t['chat_access_mode'] ?? 'public';
                        ?>
                            <tr class="tenant-row" id="row-<?php echo $sub; ?>"
                                data-subdomain="<?php echo $sub; ?>"
                                data-name="<?php echo htmlspecialchars($t['business_name']); ?>"
                                data-webhook="<?php echo htmlspecialchars($t['webhook_url']); ?>"
                                data-welcome="<?php echo htmlspecialchars($t['welcome_message']); ?>"
                                data-color="<?php echo htmlspecialchars($t['theme_color']); ?>"
                                data-plan="<?php echo htmlspecialchars($plan); ?>"
                                data-access-mode="<?php echo htmlspecialchars($accessMode); ?>"
                                data-access-key="<?php echo htmlspecialchars($t['internal_access_key'] ?? ''); ?>"
                                data-active="<?php echo $isActive ? '1' : '0'; ?>">

                                <td>
                                    <div class="tenant-name-col"><?php echo htmlspecialchars($t['business_name']); ?></div>
                                    <a href="/?subdomain=<?php echo $sub; ?>" target="_blank" class="tenant-domain-link">
                                        https://<?php echo $sub; ?>.chatmodel.in <span style="font-size: 0.72rem;">↗</span>
                                    </a>
                                </td>

                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; flex-wrap: wrap;">
                                        <span class="plan-badge <?php echo htmlspecialchars($plan); ?>"><?php echo strtoupper($plan); ?></span>
                                        <span class="access-mode-badge <?php echo $accessMode === 'private' ? 'private' : 'public'; ?>">
                                            <?php echo $accessMode === 'private' ? '🔒 Private' : '🌐 Public'; ?>
                                        </span>
                                    </div>
                                    <div class="quota-text">
                                        <?php echo number_format($monthlyConvs); ?> / <?php echo $monthlyLimit < 0 ? 'Unlimited' : number_format($monthlyLimit); ?> convs
                                    </div>
                                    <?php if ($monthlyLimit > 0): ?>
                                        <div class="conv-progress-bg">
                                            <div class="conv-progress-fill" style="width: <?php echo min(100, $usagePercent); ?>%; background: <?php echo $barColor; ?>;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; justify-content: center;">
                                        <label class="switch" title="Toggle active / paused status">
                                            <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?> onchange="toggleTenantStatus('<?php echo $sub; ?>', this.checked)">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </td>

                                <td style="text-align: right; white-space: nowrap;">
                                    <button class="btn-logs" onclick="openLogsModal('<?php echo $sub; ?>')" title="Inspect Live Chat Logs">💬 Logs</button>
                                    <button class="btn-edit" onclick="openResetSubdomainPasswordModal('<?php echo $sub; ?>')" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);" title="Reset Tenant Workspace Password">🔑 Pass</button>
                                    <button class="btn-edit" onclick="openEditModal('<?php echo $sub; ?>')" title="Edit Workspace Settings">✏️ Edit</button>
                                    <button class="btn-delete" onclick="openDeleteModal('<?php echo $sub; ?>')" title="Delete Workspace">🗑️ Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="noResults" style="display: none; text-align: center; padding: 36px 20px; color: var(--text-body); font-size: 0.9rem;">
                    <div style="font-size: 1.6rem; margin-bottom: 6px;">🔍</div>
                    No matching tenant subdomains found.
                </div>
            </div>
        </div>

        <!-- TAB 2: INQUIRIES MANAGEMENT -->
        <div id="adminTabInquiries" class="admin-tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="color: var(--text-heading); font-size: 1.1rem; font-weight: 700;">📥 Business Inquiries & Consultation Leads</h3>
                <span style="font-size: 0.85rem; color: var(--text-body);"><?php echo count($allInquiries); ?> total submissions</span>
            </div>

            <?php if (empty($allInquiries)): ?>
                <div style="text-align: center; padding: 40px 20px; color: var(--text-body); background: var(--toggle-bg); border-radius: 16px;">
                    <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                    No inquiries received yet. Submissions from the landing page contact form will appear here.
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="tenant-table">
                        <thead>
                            <tr>
                                <th>Contact & Company</th>
                                <th>Plan of Interest</th>
                                <th>Message / Requirements</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allInquiries as $inq):
                                $isNew = empty($inq['status']) || $inq['status'] === 'new';
                            ?>
                                <tr id="inq-row-<?php echo $inq['id']; ?>">
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-heading);"><?php echo htmlspecialchars($inq['name']); ?></div>
                                        <div style="font-size: 0.78rem; color: #0284c7;"><?php echo htmlspecialchars($inq['email']); ?></div>
                                        <?php if (!empty($inq['phone'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-body);">📞 <?php echo htmlspecialchars($inq['phone']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($inq['company'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-heading); font-weight: 600;">🏢 <?php echo htmlspecialchars($inq['company']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="plan-badge <?php echo htmlspecialchars($inq['plan'] ?? 'starter'); ?>">
                                            <?php echo strtoupper($inq['plan'] ?? 'Starter'); ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 320px; font-size: 0.84rem; color: var(--text-body); word-break: break-word;">
                                        <?php echo nl2br(htmlspecialchars($inq['message'])); ?>
                                    </td>
                                    <td>
                                        <span class="status-pill" style="display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: <?php echo $isNew ? 'rgba(239, 68, 68, 0.15)' : 'rgba(100, 116, 139, 0.15)'; ?>; color: <?php echo $isNew ? '#ef4444' : '#94a3b8'; ?>; border: 1px solid <?php echo $isNew ? 'rgba(239, 68, 68, 0.3)' : 'rgba(100, 116, 139, 0.3)'; ?>;">
                                            <?php echo $isNew ? 'New' : 'Read'; ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php if ($isNew): ?>
                                            <button class="btn-logs" onclick="markInquiryRead(<?php echo $inq['id']; ?>)">Mark Read</button>
                                        <?php endif; ?>
                                        <button class="btn-delete" onclick="deleteInquiry(<?php echo $inq['id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/assets/js/admin.js"></script>
    <script src="/assets/js/auth.js"></script>
</body>

</html>
