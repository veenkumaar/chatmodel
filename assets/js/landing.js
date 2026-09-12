/**
 * ChatModel SaaS - Landing Page Logic
 */

// Main Landing Day / Night Theme Management
const mainThemeBtn = document.getElementById('mainThemeBtn');
const mainThemeIcon = document.getElementById('mainThemeIcon');
const MAIN_THEME_KEY = 'chatmodel_global_theme';
const savedMainTheme = localStorage.getItem(MAIN_THEME_KEY) || 'dark';

function applyMainTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem(MAIN_THEME_KEY, theme);
    if (mainThemeIcon) mainThemeIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
    if (mainThemeBtn) mainThemeBtn.setAttribute('title', theme === 'dark' ? 'Switch to Day (Light) Mode' : 'Switch to Night (Dark) Mode');
}

applyMainTheme(savedMainTheme);

if (mainThemeBtn) {
    mainThemeBtn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyMainTheme(current === 'dark' ? 'light' : 'dark');
    });
}

// Live Demo Chat Simulation & API
const demoMessages = document.getElementById('demoMessages');
const demoInput = document.getElementById('demoInput');
const demoSessionId = 'demo_' + Math.random().toString(36).substring(2, 8);

async function sendDemoMessage() {
    if (!demoInput || !demoMessages) return;
    const text = demoInput.value.trim();
    if (!text) return;

    // Append user message
    const userDiv = document.createElement('div');
    userDiv.classList.add('demo-msg', 'user');
    userDiv.textContent = text;
    demoMessages.appendChild(userDiv);
    demoInput.value = '';
    demoMessages.scrollTop = demoMessages.scrollHeight;

    // Show temporary thinking bot message
    const botDiv = document.createElement('div');
    botDiv.classList.add('demo-msg', 'bot');
    botDiv.innerHTML = '<em>Processing inquiry through AI assistant...</em>';
    demoMessages.appendChild(botDiv);
    demoMessages.scrollTop = demoMessages.scrollHeight;

    try {
        const res = await fetch('/api/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: text,
                sessionId: demoSessionId,
                subdomain: 'demo'
            })
        });
        const data = await res.json();
        const reply = data.output || data.response || data.text || data.message || 'Assistant response generated successfully.';
        botDiv.innerHTML = DOMPurify.sanitize(marked.parse(reply));
    } catch (err) {
        botDiv.textContent = "Thank you! With ChatModel's conversational AI engine, your business can answer questions like this instantly with 0ms delay.";
    }
    demoMessages.scrollTop = demoMessages.scrollHeight;
}

if (demoInput) {
    demoInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendDemoMessage();
    });
}

// Mobile Drawer Toggle
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mobileNavDrawer = document.getElementById('mobileNavDrawer');

function toggleMobileMenu() {
    if (!mobileNavDrawer || !mobileMenuToggle) return;
    const isActive = mobileNavDrawer.classList.toggle('active');
    mobileMenuToggle.classList.toggle('active', isActive);
    mobileMenuToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
}

function closeMobileMenu() {
    if (!mobileNavDrawer || !mobileMenuToggle) return;
    mobileNavDrawer.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
}

// Close mobile menu on outside click or resize
document.addEventListener('click', (e) => {
    if (mobileNavDrawer && mobileMenuToggle) {
        if (!mobileNavDrawer.contains(e.target) && !mobileMenuToggle.contains(e.target) && mobileNavDrawer.classList.contains('active')) {
            closeMobileMenu();
        }
    }
});

window.addEventListener('resize', () => {
    if (mobileNavDrawer && window.innerWidth > 768 && mobileNavDrawer.classList.contains('active')) {
        closeMobileMenu();
    }
});

// Public Business Inquiry Form Submission
async function handleInquirySubmit(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('inqSubmitBtn');
    const alertBox = document.getElementById('inquiryAlert');
    const name = document.getElementById('inqName').value.trim();
    const email = document.getElementById('inqEmail').value.trim();
    const phone = document.getElementById('inqPhone').value.trim();
    const company = document.getElementById('inqCompany').value.trim();
    const plan = document.getElementById('inqPlan').value;
    const message = document.getElementById('inqMessage').value.trim();

    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Submitting inquiry...';
    alertBox.style.display = 'none';

    try {
        const res = await fetch('/api/tenants.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'submit_inquiry',
                name: name,
                email: email,
                phone: phone,
                company: company,
                plan: plan,
                message: message
            })
        });

        const data = await res.json();
        if (data.success) {
            alertBox.style.display = 'block';
            alertBox.style.background = 'rgba(16, 185, 129, 0.15)';
            alertBox.style.color = '#10b981';
            alertBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            alertBox.textContent = data.message || 'Thank you! Your inquiry has been submitted successfully.';
            document.getElementById('publicInquiryForm').reset();
        } else {
            alertBox.style.display = 'block';
            alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
            alertBox.style.color = '#ef4444';
            alertBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            alertBox.textContent = data.error || 'Failed to submit inquiry. Please try again.';
        }
    } catch (err) {
        alertBox.style.display = 'block';
        alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
        alertBox.style.color = '#ef4444';
        alertBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        alertBox.textContent = 'Network or server error submitting inquiry. Please try again.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = '🚀 Send Business Inquiry';
    }
}

// Plan Selection & Smooth Scroll with Notification
function selectPricingPlan(planCode, planName) {
    const planSelect = document.getElementById('inqPlan');
    if (planSelect) {
        planSelect.value = planCode;
    }

    const alertBox = document.getElementById('inquiryAlert');
    if (alertBox) {
        alertBox.style.display = 'block';
        alertBox.style.background = 'rgba(99, 102, 241, 0.15)';
        alertBox.style.color = '#818cf8';
        alertBox.style.border = '1px solid rgba(99, 102, 241, 0.35)';
        alertBox.innerHTML = `✨ <strong>${planName} Selected!</strong> Please fill out this form — our team will contact you shortly to deploy your assistant.`;
    }

    const inquirySection = document.getElementById('inquiry');
    if (inquirySection) {
        inquirySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    setTimeout(() => {
        const nameInput = document.getElementById('inqName');
        if (nameInput) {
            nameInput.focus();
        }
    }, 500);
}

// FAQ Accordion Interaction
function toggleFaq(headerElement) {
    const parentItem = headerElement.closest('.faq-item');
    if (!parentItem) return;

    const isActive = parentItem.classList.contains('active');
    
    // Close other items for single-accordion UX
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });

    if (!isActive) {
        parentItem.classList.add('active');
    }
}

