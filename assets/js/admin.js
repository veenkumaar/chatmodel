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

// Instant Live Search Filter
function filterTenants() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    const query = searchInput.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.tenant-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const subdomain = row.getAttribute('data-subdomain').toLowerCase();
        const name = row.getAttribute('data-name').toLowerCase();
        if (subdomain.includes(query) || name.includes(query)) {
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
    document.getElementById('editBusinessName').value = row.getAttribute('data-name') || '';
    document.getElementById('editWebhookUrl').value = row.getAttribute('data-webhook') || '';
    document.getElementById('editWelcomeMessage').value = row.getAttribute('data-welcome') || '';
    document.getElementById('editThemeColor').value = row.getAttribute('data-color') || '#4f46e5';
    document.getElementById('editPlan').value = row.getAttribute('data-plan') || 'starter';
    if (document.getElementById('editChatAccessMode')) {
        document.getElementById('editChatAccessMode').value = row.getAttribute('data-access-mode') || 'public';
    }
    if (document.getElementById('editInternalAccessKey')) {
        document.getElementById('editInternalAccessKey').value = row.getAttribute('data-access-key') || '';
    }

    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) modal.classList.remove('active');
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
    const businessName = document.getElementById('editBusinessName').value.trim();
    const webhookUrl = document.getElementById('editWebhookUrl').value.trim();
    const welcomeMessage = document.getElementById('editWelcomeMessage').value.trim();
    const themeColor = document.getElementById('editThemeColor').value;
    const plan = document.getElementById('editPlan').value;
    const chatAccessMode = document.getElementById('editChatAccessMode') ? document.getElementById('editChatAccessMode').value : 'public';
    const internalAccessKey = document.getElementById('editInternalAccessKey') ? document.getElementById('editInternalAccessKey').value.trim() : '';

    if (chatAccessMode === 'private' && !internalAccessKey) {
        alert('A Dedicated Internal Team Passcode / Key is mandatory when choosing Private mode.');
        const el = document.getElementById('editInternalAccessKey');
        if (el) el.focus();
        return;
    }

    const row = document.getElementById('row-' + subdomain);
    const isActive = row ? parseInt(row.getAttribute('data-active')) : 1;

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                subdomain: subdomain,
                business_name: businessName,
                webhook_url: webhookUrl,
                welcome_message: welcomeMessage,
                theme_color: themeColor,
                plan: plan,
                chat_access_mode: chatAccessMode,
                internal_access_key: internalAccessKey,
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
    const businessName = document.getElementById('newBusinessName').value.trim();
    const webhookUrl = document.getElementById('newWebhookUrl').value.trim();
    const themeColor = document.getElementById('newThemeColor').value;
    const plan = document.getElementById('newPlan').value;
    const chatAccessMode = document.getElementById('newChatAccessMode') ? document.getElementById('newChatAccessMode').value : 'public';
    const internalAccessKey = document.getElementById('newInternalAccessKey') ? document.getElementById('newInternalAccessKey').value.trim() : '';

    if (chatAccessMode === 'private' && !internalAccessKey) {
        alert('A Dedicated Internal Team Passcode / Key is mandatory when choosing Private mode.');
        const el = document.getElementById('newInternalAccessKey');
        if (el) el.focus();
        return;
    }

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create',
                subdomain: subdomain,
                business_name: businessName,
                webhook_url: webhookUrl,
                theme_color: themeColor,
                plan: plan,
                chat_access_mode: chatAccessMode,
                internal_access_key: internalAccessKey,
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

// Admin Password Management
function openAdminPasswordModal() {
    const modal = document.getElementById('adminPasswordModal');
    const form = document.getElementById('adminPasswordForm');
    const status = document.getElementById('adminPasswordStatus');
    if (form) form.reset();
    if (status) status.style.display = 'none';
    if (modal) modal.classList.add('active');
}

function closeAdminPasswordModal() {
    const modal = document.getElementById('adminPasswordModal');
    if (modal) modal.classList.remove('active');
}

async function handleAdminPasswordChange(e) {
    e.preventDefault();
    const currentPassword = document.getElementById('adminCurrentPassword').value.trim();
    const newPassword = document.getElementById('adminNewPassword').value.trim();
    const confirmPassword = document.getElementById('adminConfirmPassword').value.trim();
    const statusDiv = document.getElementById('adminPasswordStatus');

    if (newPassword.length < 6) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ New password must be at least 6 characters long.';
        return;
    }

    if (newPassword !== confirmPassword) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ New passwords do not match. Please re-type.';
        return;
    }

    statusDiv.style.display = 'block';
    statusDiv.style.background = 'rgba(99, 102, 241, 0.1)';
    statusDiv.style.color = '#818cf8';
    statusDiv.textContent = '⏳ Updating password...';

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_admin_password',
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        const data = await res.json();

        if (data.success) {
            statusDiv.style.background = 'rgba(16, 185, 129, 0.15)';
            statusDiv.style.color = '#10b981';
            statusDiv.textContent = '✅ ' + (data.message || 'Password changed successfully!');
            showToast('Admin password updated successfully!');
            setTimeout(() => {
                closeAdminPasswordModal();
            }, 1200);
        } else {
            statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
            statusDiv.style.color = '#ef4444';
            statusDiv.textContent = '❌ ' + (data.error || 'Failed to update password.');
        }
    } catch (err) {
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ Server communication error.';
    }
}

// Subdomain Password Reset Management (Admin)
function openResetSubdomainPasswordModal(subdomain) {
    const modal = document.getElementById('resetSubdomainPasswordModal');
    const form = document.getElementById('resetSubdomainPasswordForm');
    const targetInput = document.getElementById('resetTargetSubdomain');
    const display = document.getElementById('resetSubdomainDisplay');
    const status = document.getElementById('resetPasswordStatus');
    
    if (form) form.reset();
    if (status) status.style.display = 'none';
    if (targetInput) targetInput.value = subdomain;
    if (display) display.textContent = `${subdomain}.chatmodel.in`;
    if (modal) modal.classList.add('active');
}

function closeResetSubdomainPasswordModal() {
    const modal = document.getElementById('resetSubdomainPasswordModal');
    if (modal) modal.classList.remove('active');
}

async function handleResetSubdomainPassword(e) {
    e.preventDefault();
    const subdomain = document.getElementById('resetTargetSubdomain').value.trim();
    const newPassword = document.getElementById('resetNewPassword').value.trim();
    const confirmPassword = document.getElementById('resetConfirmPassword').value.trim();
    const statusDiv = document.getElementById('resetPasswordStatus');

    if (newPassword.length < 6) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ New password must be at least 6 characters long.';
        return;
    }

    if (newPassword !== confirmPassword) {
        statusDiv.style.display = 'block';
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ Passwords do not match. Please re-type.';
        return;
    }

    statusDiv.style.display = 'block';
    statusDiv.style.background = 'rgba(99, 102, 241, 0.1)';
    statusDiv.style.color = '#818cf8';
    statusDiv.textContent = `⏳ Setting new password for ${subdomain}...`;

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update_password',
                subdomain: subdomain,
                new_password: newPassword
            })
        });
        const data = await res.json();

        if (data.success) {
            statusDiv.style.background = 'rgba(16, 185, 129, 0.15)';
            statusDiv.style.color = '#10b981';
            statusDiv.textContent = `✅ Password for '${subdomain}' reset successfully!`;
            showToast(`Password for '${subdomain}' reset successfully!`);
            setTimeout(() => {
                closeResetSubdomainPasswordModal();
            }, 1200);
        } else {
            statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
            statusDiv.style.color = '#ef4444';
            statusDiv.textContent = '❌ ' + (data.error || 'Failed to update workspace password.');
        }
    } catch (err) {
        statusDiv.style.background = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.color = '#ef4444';
        statusDiv.textContent = '❌ Server communication error.';
    }
}

