/**
 * ChatModel SaaS - Tenant AI Assistant Client Logic
 */

// Internal session logout
async function handleChatLogout() {
    const subdomain = window.TENANT_SUBDOMAIN || '';
    try {
        await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'chat_logout',
                subdomain: subdomain
            })
        });
    } catch (e) {}
    window.location.reload();
}

// Authentication gate submit (Private mode)
async function handleChatLoginSubmit(e) {
    e.preventDefault();
    const passwordInput = document.getElementById('chatPasswordInput');
    const unlockBtn = document.getElementById('unlockBtn');
    const errDiv = document.getElementById('authErrorMsg');
    const subdomain = window.TENANT_SUBDOMAIN || '';
    const password = passwordInput ? passwordInput.value.trim() : '';

    if (!password) return;

    if (unlockBtn) {
        unlockBtn.disabled = true;
        unlockBtn.textContent = 'Verifying access...';
    }
    if (errDiv) errDiv.style.display = 'none';

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_chat_access',
                subdomain: subdomain,
                password: password
            })
        });
        const data = await res.json();

        if (data.success && data.authenticated) {
            window.IS_CHAT_AUTHENTICATED = true;
            const authGate = document.getElementById('authGate');
            const chatMessages = document.getElementById('chatMessages');
            const chatInputContainer = document.getElementById('chatInputContainer');
            const headerStatusText = document.getElementById('headerStatusText');

            if (authGate) authGate.style.display = 'none';
            if (chatMessages) chatMessages.style.display = 'flex';
            if (chatInputContainer) chatInputContainer.style.display = 'flex';
            if (headerStatusText) headerStatusText.textContent = 'Internal Team Session';

            const chatInput = document.getElementById('chatInput');
            if (chatInput) setTimeout(() => chatInput.focus(), 100);
        } else {
            if (errDiv) {
                errDiv.textContent = data.error || 'Invalid passcode. Please try again.';
                errDiv.style.display = 'block';
            }
            if (unlockBtn) {
                unlockBtn.disabled = false;
                unlockBtn.innerHTML = '<span>Unlock AI Assistant</span><span>➔</span>';
            }
        }
    } catch (err) {
        if (errDiv) {
            errDiv.textContent = 'Server verification failed. Please try again.';
            errDiv.style.display = 'block';
        }
        if (unlockBtn) {
            unlockBtn.disabled = false;
            unlockBtn.innerHTML = '<span>Unlock AI Assistant</span><span>➔</span>';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const sessionId = 'session_' + Math.random().toString(36).substring(2, 9);
    const subdomain = window.TENANT_SUBDOMAIN || '';

    // Day / Night Theme Preference Management
    const THEME_STORAGE_KEY = 'chatmodel_theme_' + subdomain;
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'dark';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_STORAGE_KEY, theme);
        if (themeIcon) themeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('title', theme === 'dark' ? 'Switch to Day (Light) Mode' : 'Switch to Night (Dark) Mode');
        }
    }

    applyTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
    }

    function addMessage(text, sender) {
        if (!chatMessages) return;
        const div = document.createElement('div');
        div.classList.add('message', sender);
        if (sender === 'bot') {
            div.innerHTML = DOMPurify.sanitize(marked.parse(text));
        } else {
            div.textContent = text;
        }
        if (loadingIndicator) {
            chatMessages.insertBefore(div, loadingIndicator);
        } else {
            chatMessages.appendChild(div);
        }
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function renderQuickReplies(replies) {
        if (!chatMessages) return;
        const container = document.createElement('div');
        container.classList.add('quick-replies');
        replies.forEach(reply => {
            const btn = document.createElement('button');
            btn.classList.add('quick-reply-btn');
            btn.textContent = reply;
            btn.onclick = () => sendMessage(reply);
            container.appendChild(btn);
        });
        if (loadingIndicator) {
            chatMessages.insertBefore(container, loadingIndicator);
        } else {
            chatMessages.appendChild(container);
        }
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function sendMessage(overrideText = null) {
        if (!chatInput) return;
        const text = typeof overrideText === 'string' ? overrideText : chatInput.value.trim();
        if (!text) return;

        document.querySelectorAll('.quick-replies').forEach(el => el.remove());
        addMessage(text, 'user');
        if (typeof overrideText !== 'string') chatInput.value = '';

        if (loadingIndicator) loadingIndicator.classList.add('active');
        chatInput.disabled = true;
        if (sendBtn) sendBtn.disabled = true;
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const res = await fetch('/api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: text,
                    sessionId: sessionId,
                    subdomain: subdomain
                })
            });

            const data = await res.json();
            if (loadingIndicator) loadingIndicator.classList.remove('active');
            chatInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
            chatInput.focus();

            if (res.status === 401 && data.auth_required) {
                window.location.reload();
                return;
            }

            if (!res.ok) {
                addMessage(data.error || 'Unable to connect to assistant.', 'bot');
                return;
            }

            const botReply = data.output || data.response || data.text || data.message || 'Received your inquiry.';
            addMessage(botReply, 'bot');

            if (data.quick_replies && Array.isArray(data.quick_replies)) {
                renderQuickReplies(data.quick_replies);
            }
        } catch (err) {
            if (loadingIndicator) loadingIndicator.classList.remove('active');
            chatInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
            addMessage('Connection error. Please try again.', 'bot');
        }
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', () => sendMessage());
    }
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }
});
