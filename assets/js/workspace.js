/**
 * ChatModel SaaS - Client Workspace Portal Logic
 */

// Toast notification helper for workspace
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

async function handleSaveUserWorkspace(e) {
    e.preventDefault();
    const subdomain = document.getElementById('userSubdomain').value;
    const businessName = document.getElementById('userBusinessName').value.trim();
    const themeColor = document.getElementById('userThemeColor').value;
    const welcomeMessage = document.getElementById('userWelcomeMessage').value.trim();
    const customDomain = document.getElementById('userCustomDomain').value.trim();
    const webhookUrl = document.getElementById('userWebhookUrl').value.trim();
    const crmWebhookUrl = document.getElementById('userCrmWebhookUrl').value.trim();
    const webhookSecret = document.getElementById('userWebhookSecret').value.trim();

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                subdomain: subdomain,
                business_name: businessName,
                theme_color: themeColor,
                welcome_message: welcomeMessage,
                custom_domain: customDomain,
                webhook_url: webhookUrl,
                crm_webhook_url: crmWebhookUrl,
                webhook_secret: webhookSecret
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast('Workspace settings updated successfully!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert('Error: ' + (data.error || 'Failed to update workspace settings'));
        }
    } catch (err) {
        alert('Server error updating workspace.');
    }
}

async function verifyUserDns() {
    const domain = document.getElementById('userCustomDomain').value.trim();
    const subdomain = document.getElementById('userSubdomain').value;
    const statusEl = document.getElementById('userDnsStatus');
    if (!domain) {
        alert('Please enter a custom domain (e.g. chat.yourbrand.com) first.');
        return;
    }
    statusEl.style.display = 'block';
    statusEl.style.background = 'rgba(99, 102, 241, 0.1)';
    statusEl.style.color = '#818cf8';
    statusEl.innerHTML = '🔍 Querying global DNS nameservers for CNAME record...';

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
        if (data.cname_matched) {
            statusEl.style.background = 'rgba(16, 185, 129, 0.15)';
            statusEl.style.color = '#10b981';
            statusEl.innerHTML = `✅ <strong>DNS Verified:</strong> CNAME is pointing correctly to <code>${data.detected_cname || data.required_cname}</code>. Whitelabel routing is active!`;
        } else {
            statusEl.style.background = 'rgba(239, 68, 68, 0.12)';
            statusEl.style.color = '#ef4444';
            statusEl.innerHTML = `⚠️ <strong>DNS Pending:</strong> Target '${domain}' is not yet pointing to <code>${data.required_cname}</code> (detected: <code>${data.detected_cname || 'None'}</code>).<br>${data.instructions}`;
        }
    } catch (e) {
        statusEl.innerHTML = 'DNS verification request failed.';
    }
}

async function pingUserCrm() {
    const crmUrl = document.getElementById('userCrmWebhookUrl').value.trim();
    const secret = document.getElementById('userWebhookSecret').value.trim();
    const subdomain = document.getElementById('userSubdomain').value;
    const statusEl = document.getElementById('userCrmStatus');

    if (!crmUrl) {
        alert('Please enter a CRM Webhook URL to test.');
        return;
    }

    statusEl.style.display = 'block';
    statusEl.style.background = 'rgba(99, 102, 241, 0.1)';
    statusEl.style.color = '#818cf8';
    statusEl.innerHTML = '🚀 Sending test lead payload to CRM pipeline...';

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'test_crm_pipeline',
                subdomain: subdomain,
                crm_webhook_url: crmUrl,
                webhook_secret: secret
            })
        });
        const data = await res.json();
        if (data.success) {
            statusEl.style.background = 'rgba(16, 185, 129, 0.15)';
            statusEl.style.color = '#10b981';
            statusEl.innerHTML = `✅ <strong>CRM Connected:</strong> ${data.message} (HTTP ${data.http_code})`;
        } else {
            statusEl.style.background = 'rgba(239, 68, 68, 0.12)';
            statusEl.style.color = '#ef4444';
            statusEl.innerHTML = `❌ <strong>CRM Test Failed:</strong> ${data.error}`;
        }
    } catch (e) {
        statusEl.innerHTML = 'CRM Ping request failed.';
    }
}

async function handleUpdateUserPassword(e) {
    e.preventDefault();
    const subdomain = document.getElementById('userSubdomain').value;
    const newPassword = document.getElementById('userNewPassword').value.trim();

    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long.');
        return;
    }

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
            showToast('Password updated successfully!');
            document.getElementById('userNewPassword').value = '';
        } else {
            alert('Error: ' + (data.error || 'Failed to update password'));
        }
    } catch (err) {
        alert('Server error updating password.');
    }
}

async function refreshUserChatLogs() {
    const subdomain = document.getElementById('userSubdomain').value;
    const container = document.getElementById('userLogsContainer');
    container.innerHTML = '<div style="text-align: center; padding: 24px; color: var(--text-body);">Refreshing conversation logs...</div>';
    try {
        const res = await fetch(`/api/tenants.php?action=get_logs&subdomain=${encodeURIComponent(subdomain)}&limit=50`);
        const data = await res.json();
        if (data.success && data.logs && data.logs.length > 0) {
            let html = '<table class="tenant-table"><thead><tr><th>Time</th><th>Sender</th><th>Message Content</th><th>Session ID</th></tr></thead><tbody>';
            data.logs.forEach(log => {
                const sender = (log.sender || 'user').toLowerCase();
                const isUser = ['user', 'customer', 'client'].includes(sender);
                const senderBadge = isUser ? '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: rgba(99, 102, 241, 0.15); color: #818cf8;">👤 Customer</span>' : '<span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: rgba(16, 185, 129, 0.15); color: #10b981;">🤖 Assistant</span>';
                html += `<tr>
                    <td style="font-size: 0.76rem; color: var(--text-body); white-space: nowrap;">${log.created_at}</td>
                    <td>${senderBadge}</td>
                    <td style="font-size: 0.85rem; color: var(--text-heading); max-width: 380px; word-break: break-word;">${log.message || ''}</td>
                    <td style="font-family: 'JetBrains Mono', monospace; font-size: 0.74rem; color: #0284c7;">${(log.session_id || '').substring(0, 14)}...</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
            showToast('Conversation logs refreshed!');
        } else {
            container.innerHTML = '<div style="text-align: center; padding: 36px 20px; color: var(--text-body); font-size: 0.9rem;">No chat conversations recorded yet.</div>';
        }
    } catch (err) {
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: #ef4444;">Failed to load logs.</div>';
    }
}
