<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - ChatModel SaaS</title>
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
                    <label>Custom CNAME Whitelabel Domain (e.g. chat.yourbrand.com)</label>
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px;">
                        <input type="text" id="editCustomDomain" class="form-control" placeholder="chat.yourbrand.com">
                        <button type="button" onclick="verifyDnsDomain('edit')" class="btn-logs" style="padding: 0 12px; font-size: 0.78rem;">⚡ Test DNS</button>
                    </div>
                </div>
                <div id="editDnsStatus" style="font-size: 0.76rem; display: none; line-height: 1.4; padding: 6px 10px; border-radius: 8px; margin-bottom: 12px;"></div>

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
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-heading); margin-bottom: 8px;">CRM Lead Capture Pipeline Integration</div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 0.75rem;">CRM Webhook URL</label>
                        <input type="url" id="editCrmWebhookUrl" class="form-control" placeholder="https://api.crm.com/leads">
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label style="font-size: 0.75rem;">CRM Bearer Secret Token</label>
                        <input type="text" id="editWebhookSecret" class="form-control" placeholder="sec_live_...">
                    </div>
                    <button type="button" onclick="testCrmPipeline('edit')" class="btn-logs" style="width: 100%; font-size: 0.78rem; justify-content: center; padding: 6px;">⚡ Send Test Lead Ping</button>
                    <div id="editCrmStatus" style="font-size: 0.76rem; display: none; line-height: 1.4; padding: 6px 10px; border-radius: 8px; margin-top: 8px;"></div>
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
                <div style="font-size: 0.85rem; color: var(--text-body);">Manage all tenant webhook pipelines, CRM sync, conversation quotas, and service states.</div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="admin-tag">🛡️ <?php echo htmlspecialchars($adminUser); ?> (Global Admin)</span>
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
                            <p>Deploy an isolated assistant workspace with custom routing, CRM pipeline, and quota tier</p>
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

                    <!-- Row 3: Whitelabel CNAME & CRM Pipeline -->
                    <div class="form-field">
                        <label>
                            <span>Custom CNAME Domain</span>
                            <span class="optional-tag">Whitelabel</span>
                        </label>
                        <div class="standard-input-box">
                            <span class="input-icon">🌐</span>
                            <input type="text" id="newCustomDomain" placeholder="chat.yourcompany.com" style="font-family: 'JetBrains Mono', monospace;">
                        </div>
                    </div>

                    <div class="form-field">
                        <label>
                            <span>CRM Lead Webhook</span>
                            <span class="optional-tag">HubSpot / Zoho</span>
                        </label>
                        <div class="standard-input-box">
                            <span class="input-icon">📩</span>
                            <input type="url" id="newCrmWebhookUrl" placeholder="https://api.crm.com/leads" style="font-family: 'JetBrains Mono', monospace; font-size: 0.84rem;">
                        </div>
                    </div>

                    <!-- Row 4: Theme Color & CRM Bearer Token -->
                    <div class="form-field">
                        <label>Brand Theme Color</label>
                        <div class="color-picker-box">
                            <span class="input-icon">🎨</span>
                            <input type="color" id="newThemeColor" value="#4f46e5" oninput="document.getElementById('newThemeColorHex').value = this.value">
                            <input type="text" id="newThemeColorHex" class="color-hex-text" value="#4f46e5">
                        </div>
                    </div>

                    <div class="form-field">
                        <label>
                            <span>CRM Secret / Bearer Token</span>
                            <span class="optional-tag">Auth</span>
                        </label>
                        <div class="standard-input-box">
                            <span class="input-icon">🔒</span>
                            <input type="text" id="newWebhookSecret" placeholder="sec_live_token_123">
                        </div>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <span class="security-note">🔒 SSL Certificate will be provisioned automatically for new subdomains</span>
                    <button type="submit" class="btn-deploy-tenant">
                        <span>Deploy Dedicated Subdomain</span>
                        <span>➔</span>
                    </button>
                </div>
            </form>

            <!-- Search & Filter Bar -->
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by subdomain, brand name, custom domain, or plan..." oninput="filterTenants()">
            </div>

            <!-- Tenants Table -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-heading);">
                    Active Workspaces (<span id="tenantCount"><?php echo count($allTenants); ?></span>)
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="tenant-table">
                    <thead>
                        <tr>
                            <th>Subdomain & Brand</th>
                            <th>Plan & Quota</th>
                            <th>Custom Domain</th>
                            <th>Status (Kill-Switch)</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tenantTableBody">
                        <?php foreach ($allTenants as $t):
                            $sub = htmlspecialchars($t['subdomain']);
                            $isActive = (int) $t['is_active'] === 1;
                            $hasCustom = !empty($t['custom_domain']);
                            $stats = $t['stats'] ?? Database::getTenantConversationStats($t['subdomain']);
                            $plan = $stats['plan'] ?? $t['plan'] ?? 'starter';
                            $monthlyConvs = (int) ($stats['monthly_conversations'] ?? 0);
                            $monthlyLimit = (int) ($stats['monthly_limit'] ?? $t['monthly_limit'] ?? 2500);
                            $usagePercent = (float) ($stats['usage_percent'] ?? 0);
                            $barColor = $usagePercent > 90 ? '#ef4444' : ($usagePercent > 70 ? '#f59e0b' : '#10b981');
                        ?>
                            <tr class="tenant-row" id="row-<?php echo $sub; ?>"
                                data-subdomain="<?php echo $sub; ?>"
                                data-name="<?php echo htmlspecialchars($t['business_name']); ?>"
                                data-custom-domain="<?php echo htmlspecialchars($t['custom_domain'] ?? ''); ?>"
                                data-webhook="<?php echo htmlspecialchars($t['webhook_url']); ?>"
                                data-welcome="<?php echo htmlspecialchars($t['welcome_message']); ?>"
                                data-color="<?php echo htmlspecialchars($t['theme_color']); ?>"
                                data-plan="<?php echo htmlspecialchars($plan); ?>"
                                data-crm-url="<?php echo htmlspecialchars($t['crm_webhook_url'] ?? ''); ?>"
                                data-crm-secret="<?php echo htmlspecialchars($t['webhook_secret'] ?? ''); ?>"
                                data-active="<?php echo $isActive ? '1' : '0'; ?>">

                                <td>
                                    <div class="tenant-name-col"><?php echo htmlspecialchars($t['business_name']); ?></div>
                                    <a href="/?subdomain=<?php echo $sub; ?>" target="_blank" class="tenant-domain-link">
                                        https://<?php echo $sub; ?>.chatmodel.in ↗
                                    </a>
                                </td>

                                <td style="min-width: 150px;">
                                    <span class="plan-badge <?php echo htmlspecialchars($plan); ?>"><?php echo strtoupper($plan); ?></span>
                                    <div style="font-size: 0.75rem; color: var(--text-body);">
                                        <?php echo number_format($monthlyConvs); ?> / <?php echo $monthlyLimit < 0 ? '∞' : number_format($monthlyLimit); ?> convs
                                    </div>
                                    <?php if ($monthlyLimit > 0): ?>
                                        <div class="conv-progress-bg">
                                            <div class="conv-progress-fill" style="width: <?php echo min(100, $usagePercent); ?>%; background: <?php echo $barColor; ?>;"></div>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($hasCustom): ?>
                                        <span style="color: #0284c7; font-family: 'JetBrains Mono', monospace; font-size: 0.78rem;">
                                            🌐 <?php echo htmlspecialchars($t['custom_domain']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-body); font-size: 0.78rem;">None (Default)</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <label class="switch" title="Toggle active status">
                                        <input type="checkbox" <?php echo $isActive ? 'checked' : ''; ?> onchange="toggleTenantStatus('<?php echo $sub; ?>', this.checked)">
                                        <span class="slider"></span>
                                    </label>
                                </td>

                                <td style="text-align: right; white-space: nowrap;">
                                    <button class="btn-logs" onclick="openLogsModal('<?php echo $sub; ?>')" title="Inspect Live Chat Logs">Logs</button>
                                    <button class="btn-edit" onclick="openEditModal('<?php echo $sub; ?>')" title="Edit Workspace Settings">Edit</button>
                                    <button class="btn-delete" onclick="openDeleteModal('<?php echo $sub; ?>')" title="Delete Workspace">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="noResults" style="display: none; text-align: center; padding: 24px; color: var(--text-body); font-size: 0.9rem;">
                    No matching subdomains found.
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
