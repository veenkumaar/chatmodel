/**
 * ChatModel SaaS - Tenant AI Assistant Client Logic
 */

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
        const div = document.createElement('div');
        div.classList.add('message', sender);
        if (sender === 'bot') {
            div.innerHTML = DOMPurify.sanitize(marked.parse(text));
        } else {
            div.textContent = text;
        }
        chatMessages.insertBefore(div, loadingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function renderQuickReplies(replies) {
        const container = document.createElement('div');
        container.classList.add('quick-replies');
        replies.forEach(reply => {
            const btn = document.createElement('button');
            btn.classList.add('quick-reply-btn');
            btn.textContent = reply;
            btn.onclick = () => sendMessage(reply);
            container.appendChild(btn);
        });
        chatMessages.insertBefore(container, loadingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function sendMessage(overrideText = null) {
        const text = typeof overrideText === 'string' ? overrideText : chatInput.value.trim();
        if (!text) return;

        document.querySelectorAll('.quick-replies').forEach(el => el.remove());
        addMessage(text, 'user');
        if (typeof overrideText !== 'string') chatInput.value = '';

        loadingIndicator.classList.add('active');
        chatInput.disabled = true;
        sendBtn.disabled = true;
        chatMessages.scrollTop = chatMessages.scrollHeight;

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
            loadingIndicator.classList.remove('active');
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();

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
            loadingIndicator.classList.remove('active');
            chatInput.disabled = false;
            sendBtn.disabled = false;
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
