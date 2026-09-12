/**
 * ChatModel SaaS - Auth / Login Page Script
 */

document.addEventListener('DOMContentLoaded', () => {
    const adminThemeBtn = document.getElementById('adminThemeBtn');
    const adminThemeIcon = document.getElementById('adminThemeIcon');
    const ADMIN_THEME_KEY = 'chatmodel_global_theme';
    const savedAdminTheme = localStorage.getItem(ADMIN_THEME_KEY) || 'dark';

    function applyAdminTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(ADMIN_THEME_KEY, theme);
        if (adminThemeIcon) adminThemeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
        if (adminThemeBtn) adminThemeBtn.setAttribute('title', theme === 'dark' ? 'Switch to Day (Light) Mode' : 'Switch to Night (Dark) Mode');
    }

    applyAdminTheme(savedAdminTheme);

    if (adminThemeBtn) {
        adminThemeBtn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            applyAdminTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
});

function openForgotHelpModal() {
    const modal = document.getElementById('forgotHelpModal');
    if (modal) modal.classList.add('active');
}

function closeForgotHelpModal() {
    const modal = document.getElementById('forgotHelpModal');
    if (modal) modal.classList.remove('active');
}
