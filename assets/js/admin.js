/**
 * ChatModel SaaS - Global Admin Management Console Logic
 */

// Toast Notifications
function showToast(message) {
    let toast = document.getElementById('toastNotification');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toastNotification';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Admin Tab Switching
function switchAdminTab(tabName) {
    const btnSubdomains = document.getElementById('tabBtnSubdomains');
    const btnInquiries = document.getElementById('tabBtnInquiries');
    const contentSubdomains = document.getElementById('adminTabSubdomains');
    const contentInquiries = document.getElementById('adminTabInquiries');

    if (tabName === 'subdomains') {
        if (btnSubdomains) btnSubdomains.classList.add('active');
        if (btnInquiries) btnInquiries.classList.remove('active');
        if (contentSubdomains) contentSubdomains.classList.add('active');
        if (contentInquiries) contentInquiries.classList.remove('active');
    } else {
        if (btnInquiries) btnInquiries.classList.add('active');
        if (btnSubdomains) btnSubdomains.classList.remove('active');
        if (contentInquiries) contentInquiries.classList.add('active');
        if (contentSubdomains) contentSubdomains.classList.remove('active');
    }
}

// Handle Subdomain Slug Input & Live Availability
let checkTimer = null;
function handleSubdomainInput(val) {
    const clean = val.toLowerCase().replace(/[^a-z0-9-]/g, '');
    const input = document.getElementById('newSubdomain');
    if (input) input.value = clean;
    checkSubdomainAvailability(clean);
}

async function checkSubdomainAvailability(slug) {
    clearTimeout(checkTimer);
    const statusEl = document.getElementById('subdomainCheckStatus');
    if (!statusEl) return;
    const cleanSlug = slug.toLowerCase().replace(/[^a-z0-9-]/g, '');

    if (cleanSlug.length < 3) {
        statusEl.style.display = 'none';
        return;
    }

    statusEl.style.display = 'block';
    statusEl.style.color = '#94a3b8';
    statusEl.textContent = 'Checking availability...';

    checkTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/api/tenants.php?action=check_subdomain&subdomain=${encodeURIComponent(cleanSlug)}`);
            const data = await res.json();
            if (data.available) {
                statusEl.style.color = '#10b981';
                statusEl.textContent = `✨ ${cleanSlug}.chatmodel.in is available!`;
            } else {
                statusEl.style.color = '#ef4444';
                statusEl.textContent = `❌ ${data.reason || 'Subdomain is not available'}`;
            }
        } catch (err) {
            statusEl.style.display = 'none';
        }
    }, 300);
}

// Verify Custom Domain CNAME DNS
async function verifyDnsDomain(mode) {
    const domainInput = mode === 'edit' ? document.getElementById('editCustomDomain') : document.getElementById('newCustomDomain');
    const subdomain = mode === 'edit' ? document.getElementById('editSubdomain').value : document.getElementById('newSubdomain').value;
    const statusDiv = mode === 'edit' ? document.getElementById('editDnsStatus') : null;
    const domain = domainInput ? domainInput.value.trim() : '';

    if (!domain) {
        alert('Please enter a custom domain first (e.g. chat.yourbrand.com).');
        return;
    }

    if (statusDiv) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = 'rgba(2, 132, 199, 0.1)';
        statusDiv.style.color = '#0284c7';
        statusDiv.textContent = '⏳ Querying global DNS records for ' + domain + '...';
    }

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_custom_domain',
                custom_domain: domain,
                subdomain: subdomain
            })
        });
        const data = await res.json();

        if (data.success) {
            if (statusDiv) {
                if (data.cname_matched) {
                    statusDiv.style.background = 'rgba(16, 185, 129, 0.15)';
                    statusDiv.style.color = '#10b981';
                    statusDiv.innerHTML = `✅ <strong>DNS Verified!</strong> CNAME correctly points to <code>${data.detected_cname}</code>`;
                } else {
                    statusDiv.style.background = 'rgba(245, 158, 11, 0.15)';
                    statusDiv.style.color = '#f59e0b';
                    statusDiv.innerHTML = `⚠️ <strong>DNS Pending:</strong> ${data.instructions}<br>Current target: <code>${data.detected_cname || data.detected_ip || 'None detected yet'}</code>`;
                }
            } else {
                showToast(data.cname_matched ? '✅ CNAME DNS Verified!' : '⚠️ CNAME DNS Pending propagation');
            }
        } else {
            if (statusDiv) {
                statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
                statusDiv.style.color = '#ef4444';
                statusDiv.textContent = '❌ ' + (data.error || 'DNS Lookup failed.');
            }
        }
    } catch (err) {
        if (statusDiv) {
            statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
            statusDiv.style.color = '#ef4444';
            statusDiv.textContent = '❌ DNS test query failed.';
        }
    }
}

// Instant Live Search Filter
function filterTenants() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    const query = searchInput.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.tenant-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const subdomain = row.getAttribute('data-subdomain').toLowerCase();
        const customDomain = (row.getAttribute('data-custom-domain') || '').toLowerCase();
        const name = row.getAttribute('data-name').toLowerCase();
        if (subdomain.includes(query) || name.includes(query) || customDomain.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countEl = document.getElementById('tenantCount');
    if (countEl) countEl.textContent = visibleCount;
    const noRes = document.getElementById('noResults');
    if (noRes) noRes.style.display = visibleCount === 0 ? 'block' : 'none';
}

// Modals
function openEditModal(subdomain) {
    const row = document.getElementById('row-' + subdomain);
    if (!row) return;

    document.getElementById('editSubdomain').value = subdomain;
    document.getElementById('editSubdomainLabel').textContent = subdomain + '.chatmodel.in';
    document.getElementById('editCustomDomain').value = row.getAttribute('data-custom-domain') || '';
    document.getElementById('editBusinessName').value = row.getAttribute('data-name') || '';
    document.getElementById('editWebhookUrl').value = row.getAttribute('data-webhook') || '';
    document.getElementById('editWelcomeMessage').value = row.getAttribute('data-welcome') || '';
    document.getElementById('editThemeColor').value = row.getAttribute('data-color') || '#4f46e5';
    document.getElementById('editPlan').value = row.getAttribute('data-plan') || 'starter';
    document.getElementById('editCrmWebhookUrl').value = row.getAttribute('data-crm-url') || '';
    document.getElementById('editWebhookSecret').value = row.getAttribute('data-crm-secret') || '';
    document.getElementById('editCrmStatus').style.display = 'none';
    document.getElementById('editDnsStatus').style.display = 'none';

    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.classList.remove('active');
}

// Test CRM Webhook Pipeline
async function testCrmPipeline(mode) {
    const urlInput = mode === 'edit' ? document.getElementById('editCrmWebhookUrl') : document.getElementById('newCrmWebhookUrl');
    const secretInput = mode === 'edit' ? document.getElementById('editWebhookSecret') : document.getElementById('newWebhookSecret');
    const statusDiv = mode === 'edit' ? document.getElementById('editCrmStatus') : null;

    const crmUrl = urlInput ? urlInput.value.trim() : '';
    const secret = secretInput ? secretInput.value.trim() : '';

    if (!crmUrl) {
        alert('Please enter a valid CRM Webhook URL first.');
        return;
    }

    if (statusDiv) {
        statusDiv.style.display = 'block';
        statusDiv.style.color = '#0284c7';
        statusDiv.textContent = '⏳ Dispatching verification payload to CRM pipeline...';
    }

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'test_crm_pipeline',
                crm_webhook_url: crmUrl,
                webhook_secret: secret,
                subdomain: 'test'
            })
        });
        const data = await res.json();

        if (data.success) {
            const msg = `✅ CRM Connected! HTTP ${data.http_code} (${data.latency_ms}ms)`;
            if (statusDiv) {
                statusDiv.style.color = '#10b981';
                statusDiv.textContent = msg;
            } else {
                showToast(msg);
            }
        } else {
            const errMsg = `❌ CRM Ping Failed: ${data.error || 'Check endpoint URL'}`;
            if (statusDiv) {
                statusDiv.style.color = '#ef4444';
                statusDiv.textContent = errMsg;
            } else {
                alert(errMsg);
            }
        }
    } catch (err) {
        if (statusDiv) {
            statusDiv.style.color = '#ef4444';
            statusDiv.textContent = '❌ Failed to reach backend test dispatcher.';
        }
    }
}

// Conversation Logs Modal Inspector
async function openLogsModal(subdomain) {
    const logsModal = document.getElementById('logsModal');
    const logsSubdomainDisplay = document.getElementById('logsSubdomainDisplay');
    const logsStatsBar = document.getElementById('logsStatsBar');
    const logsContainer = document.getElementById('logsContainer');

    logsSubdomainDisplay.textContent = subdomain + '.chatmodel.in';
    logsStatsBar.innerHTML = '<em>Fetching conversation metrics...</em>';
    logsContainer.innerHTML = '<div style="text-align: center; padding: 30px; color: var(--text-body);">Loading session logs...</div>';
    logsModal.classList.add('active');

    try {
        const res = await fetch(`/api/tenants.php?action=get_logs&subdomain=${encodeURIComponent(subdomain)}`);
        const data = await res.json();

        if (data.success) {
            const stats = data.stats;
            const limitText = stats.monthly_limit < 0 ? 'Unlimited' : stats.monthly_limit.toLocaleString();
            logsStatsBar.innerHTML = `
                <div><strong>Plan:</strong> <span class="plan-badge ${stats.plan}">${stats.plan.toUpperCase()}</span></div>
                <div><strong>This Month:</strong> ${stats.monthly_conversations} / ${limitText} conversations</div>
                <div><strong>Total Messages:</strong> ${stats.all_time_messages}</div>
            `;

            if (!data.logs || data.logs.length === 0) {
                logsContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-body);">
                        <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
                        No chat messages recorded yet for <strong>${subdomain}</strong>.
                    </div>
                `;
            } else {
                let html = '';
                data.logs.forEach(log => {
                    const isBot = log.sender === 'bot';
                    const senderLabel = isBot ? '🤖 AI Concierge' : '👤 Visitor';
                    const time = log.created_at || '';
                    html += `
                        <div class="chat-log-item ${isBot ? 'bot' : 'user'}">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px;">
                                <span><strong>${senderLabel}</strong> (Session: <code>${(log.session_id || '').substring(0, 10)}...</code>)</span>
                                <span>${time}</span>
                            </div>
                            <div>${escapeHtml(log.message || '')}</div>
                        </div>
                    `;
                });
                logsContainer.innerHTML = html;
            }
        } else {
            logsContainer.innerHTML = `<div style="color: #ef4444; padding: 20px;">Failed to load logs: ${data.error}</div>`;
        }
    } catch (err) {
        logsContainer.innerHTML = `<div style="color: #ef4444; padding: 20px;">Error retrieving logs from server.</div>`;
    }
}

function closeLogsModal() {
    const modal = document.getElementById('logsModal');
    if (modal) modal.classList.remove('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Delete Modal Logic
function openDeleteModal(subdomain) {
    document.getElementById('deleteTargetSubdomain').value = subdomain;
    document.getElementById('deleteSubdomainDisplay').textContent = subdomain + '.chatmodel.in';
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    if (modal) modal.classList.remove('active');
}

async function executeDeleteTenant() {
    const subdomain = document.getElementById('deleteTargetSubdomain').value;
    if (!subdomain) return;

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete',
                subdomain: subdomain
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Subdomain '${subdomain}.chatmodel.in' deleted!`);
            closeDeleteModal();
            const row = document.getElementById('row-' + subdomain);
            if (row) row.remove();
            const remaining = document.querySelectorAll('.tenant-row').length;
            const countEl = document.getElementById('tenantCount');
            if (countEl) countEl.textContent = remaining;
        } else {
            alert('Error: ' + (data.error || 'Failed to delete tenant'));
        }
    } catch (err) {
        alert('Server error deleting tenant.');
    }
}

async function handleSaveEdit(e) {
    e.preventDefault();
    const subdomain = document.getElementById('editSubdomain').value;
    const customDomain = document.getElementById('editCustomDomain').value.trim();
    const businessName = document.getElementById('editBusinessName').value.trim();
    const webhookUrl = document.getElementById('editWebhookUrl').value.trim();
    const welcomeMessage = document.getElementById('editWelcomeMessage').value.trim();
    const themeColor = document.getElementById('editThemeColor').value;
    const plan = document.getElementById('editPlan').value;
    const crmWebhookUrl = document.getElementById('editCrmWebhookUrl').value.trim();
    const webhookSecret = document.getElementById('editWebhookSecret').value.trim();

    const row = document.getElementById('row-' + subdomain);
    const isActive = row ? parseInt(row.getAttribute('data-active')) : 1;

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                subdomain: subdomain,
                custom_domain: customDomain,
                business_name: businessName,
                webhook_url: webhookUrl,
                welcome_message: welcomeMessage,
                theme_color: themeColor,
                plan: plan,
                crm_webhook_url: crmWebhookUrl,
                webhook_secret: webhookSecret,
                is_active: isActive
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Dedicated Subdomain '${subdomain}' updated!`);
            closeEditModal();
            setTimeout(() => window.location.reload(), 900);
        } else {
            alert('Error: ' + (data.error || 'Failed to update tenant'));
        }
    } catch (err) {
        alert('Server communication error.');
    }
}

async function toggleTenantStatus(subdomain, isChecked) {
    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'toggle_status',
                subdomain: subdomain,
                is_active: isChecked ? 1 : 0
            })
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('row-' + subdomain);
            if (row) row.setAttribute('data-active', isChecked ? 1 : 0);
            showToast(`Subdomain '${subdomain}' is now ${isChecked ? 'ENABLED' : 'PAUSED / DISABLED'}`);
        } else {
            alert('Error: ' + (data.error || 'Failed to update status'));
        }
    } catch (err) {
        alert('Server error updating status.');
    }
}

async function handleCreateTenant(e) {
    e.preventDefault();
    const subdomain = document.getElementById('newSubdomain').value.trim();
    const customDomain = document.getElementById('newCustomDomain').value.trim();
    const businessName = document.getElementById('newBusinessName').value.trim();
    const webhookUrl = document.getElementById('newWebhookUrl').value.trim();
    const themeColor = document.getElementById('newThemeColor').value;
    const plan = document.getElementById('newPlan').value;
    const crmWebhookUrl = document.getElementById('newCrmWebhookUrl').value.trim();
    const webhookSecret = document.getElementById('newWebhookSecret').value.trim();

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create',
                subdomain: subdomain,
                custom_domain: customDomain,
                business_name: businessName,
                webhook_url: webhookUrl,
                theme_color: themeColor,
                plan: plan,
                crm_webhook_url: crmWebhookUrl,
                webhook_secret: webhookSecret,
                is_active: 1
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast(`Dedicated Subdomain '${subdomain}.chatmodel.in' provisioned!`);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Failed to create tenant'));
        }
    } catch (err) {
        alert('Server communication error.');
    }
}

// Inquiries actions
async function markInquiryRead(id) {
    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'mark_inquiry_read',
                id: id
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Inquiry marked as read');
            const row = document.getElementById('inq-row-' + id);
            if (row) {
                const statusBadge = row.querySelector('.status-pill');
                if (statusBadge) {
                    statusBadge.style.background = 'rgba(100, 116, 139, 0.15)';
                    statusBadge.style.color = '#94a3b8';
                    statusBadge.style.border = '1px solid rgba(100, 116, 139, 0.3)';
                    statusBadge.textContent = 'Read';
                }
            }
        }
    } catch (e) {
        alert('Failed to update inquiry status');
    }
}

async function deleteInquiry(id) {
    if (!confirm('Are you sure you want to delete this business inquiry?')) return;
    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'delete_inquiry',
                id: id
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Inquiry deleted');
            const row = document.getElementById('inq-row-' + id);
            if (row) row.remove();
        }
    } catch (e) {
        alert('Failed to delete inquiry');
    }
}
