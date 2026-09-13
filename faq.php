<?php
require_once __DIR__ . '/db/security.php';
if (Security::wantsMarkdown()) {
    $md = <<<MD
# Frequently Asked Questions (FAQ) - ChatModel

## General & Platform Architecture

### What is ChatModel?
ChatModel is an enterprise multi-tenant conversational AI platform. We provide dedicated 24/7 AI assistants operating on custom branded subdomains, real-time lead qualification, action workflows, and native webhook integration with platforms like n8n, Make, and internal enterprise systems.

### How do dedicated client subdomains work?
Each tenant/client receives a private, isolated workspace hosted on their own unique subdomain (e.g. `clientname.chatmodel.in`). All configurations, styling, session memory, and webhooks are segregated.

### Can I embed the AI Assistant onto my existing website?
Yes. You can embed your ChatModel assistant directly onto any WordPress, Shopify, Webflow, React, or custom HTML site using a responsive iframe widget or via direct API integration.

## Automation & Webhook Integration

### Which automation tools can connect with ChatModel?
ChatModel connects seamlessly with any webhook-enabled platform, including n8n, Make.com, Zapier, Pabbly, custom Node.js/Python microservices, and enterprise REST APIs.

### Does ChatModel support streaming responses (SSE)?
Yes! ChatModel supports Server-Sent Events (SSE) streaming for real-time, token-by-token conversational streaming.

## Security & Data Privacy

### Where is conversation data stored?
Data is isolated per tenant in hardened SQLite WAL databases with strict access control and SSL/TLS encryption.

### Is customer conversational data shared or used to train public models?
No. Your business workflows, customer inquiries, and proprietary data remain strictly private to your account.
MD;
    Security::respondWithMarkdown($md);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-RH4ZFSCEVS"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-RH4ZFSCEVS');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions (FAQ) - ChatModel.in</title>
    <meta name="description"
        content="Frequently Asked Questions about ChatModel.in. Learn about our multi-tenant conversational AI platform, custom subdomains, webhook automation, WhatsApp/social integration, and security.">
    <link rel="canonical" href="https://chatmodel.in/faq.php">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css">
    <style>
        .faq-page-wrapper {
            max-width: 920px;
            margin: 40px auto 80px auto;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.05);
        }

        .faq-page-header {
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 24px;
            margin-bottom: 32px;
        }

        .faq-page-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 8px;
        }

        .faq-page-meta {
            font-size: 0.95rem;
            color: var(--text-body);
            line-height: 1.6;
        }

        .faq-category {
            margin-bottom: 36px;
        }

        .faq-category-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .faq-category-title span {
            background: rgba(99, 102, 241, 0.12);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .back-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 24px;
            transition: transform 0.2s ease;
        }

        .back-nav:hover {
            transform: translateX(-3px);
        }

        .faq-search-box {
            margin-bottom: 30px;
            position: relative;
        }

        .faq-search-input {
            width: 100%;
            padding: 14px 20px 14px 44px;
            background: var(--input-bg, rgba(255, 255, 255, 0.04));
            border: 1px solid var(--card-border);
            border-radius: 14px;
            color: var(--text-heading);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .faq-search-input:focus {
            border-color: var(--primary);
        }

        .faq-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-body);
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .faq-page-wrapper {
                padding: 28px 20px;
                margin: 20px auto 60px auto;
            }

            .faq-page-header h1 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body>
    <div class="ambient-glow"></div>

    <div class="container">
        <!-- Navigation -->
        <nav>
            <a href="/" class="logo">
                <div class="logo-badge">⚡</div>
                ChatModel<span class="gradient-text">.in</span>
            </a>

            <div class="nav-actions">
                <a href="/login.php" class="btn-portal desktop-only">🔐 Client Portal</a>
                <button id="mainThemeBtn" class="theme-switch-btn" title="Toggle Day / Night Mode"
                    aria-label="Toggle theme">
                    <span id="mainThemeIcon">☀️</span>
                </button>
            </div>
        </nav>

        <!-- Main FAQ Container -->
        <main class="faq-page-wrapper">
            <a href="/" class="back-nav">← Back to Homepage</a>

            <div class="faq-page-header">
                <h1>Frequently Asked Questions</h1>
                <p class="faq-page-meta">
                    Find quick answers to common questions about setting up dedicated workspaces, webhook automation, AI
                    capabilities, WhatsApp/social integrations, and security.
                </p>
            </div>

            <!-- FAQ Live Filter/Search -->
            <div class="faq-search-box">
                <span class="faq-search-icon">🔍</span>
                <input type="text" id="faqSearchInput" class="faq-search-input"
                    placeholder="Search questions (e.g. subdomains, webhooks, WhatsApp, pricing, security)..."
                    oninput="filterFaq()">
            </div>

            <!-- CATEGORY 1: Platform & Dedicated Subdomains -->
            <div class="faq-category">
                <div class="faq-category-title">
                    <span>🌐</span> Workspace Architecture & Subdomains
                </div>
                <div class="faq-container">
                    <div class="faq-item active">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>How quickly can I provision and launch my dedicated workspace?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Workspaces are provisioned in <strong>under 3 seconds</strong> with 0-downtime. Your
                                dedicated subdomain (e.g., <code>yourbrand.chatmodel.in</code>) receives automated
                                wildcard SSL certificates and becomes instantly accessible worldwide without manual DNS
                                delays.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>What is the difference between Public and Private Chat Access modes?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p><strong>Public Mode</strong> allows any website visitor or customer to converse freely
                                with your assistant without authentication (ideal for customer service, marketing, and
                                lead generation). <strong>Private Mode</strong> locks the chat interface behind a
                                dedicated internal passcode or workspace credentials, making it perfect for company
                                internal staff and confidential tools.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I customize the branding, colors, and welcome greeting?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes! Every dedicated workspace has full control over its business title, hex theme accent
                                color, custom welcome greeting, and light/dark theme presets. The chat interface
                                automatically renders with your brand's unique visual identity.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I embed the chatbot widget on my existing website?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes, your assistant can be directly embedded using a standard responsive iframe or linked
                                directly via your dedicated subdomain URL on any website, CMS (WordPress, Webflow,
                                Shopify), or web application.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CATEGORY 3: Quotas, Plans & Security -->
            <div class="faq-category">
                <div class="faq-category-title">
                    <span>🔒</span> Plans, Quotas & Security
                </div>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>What happens when my monthly conversation quota is reached?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>You can track your live conversation utilization in your dedicated Client Portal
                                dashboard. When nearing your tier limit, you can upgrade your plan seamlessly with zero
                                service interruption. Enterprise plans include <strong>unlimited conversations</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Is our client conversation data private and secure?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Each workspace operates in an isolated tenant container. Subdomain portals and live
                                chat sessions are protected with strict <code>noindex</code> robots directives and
                                HTTP-level anti-scraping tags to ensure confidential company information is never
                                indexed by public search engines.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can I pause or disable a client's assistant temporarily?</span>
                            <span class="faq-icon">+</span>
                        </div>
                        <div class="faq-answer">
                            <p>Yes. Workspace administrators can toggle active/inactive status at any time with one
                                click. When disabled, the subdomain displays a clean maintenance screen and pauses
                                incoming webhook calls.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                style="margin-top: 40px; padding: 24px; background: rgba(99, 102, 241, 0.06); border: 1px solid var(--card-border); border-radius: 16px; text-align: center;">
                <h3 style="color: var(--text-heading); font-size: 1.15rem; margin-bottom: 8px;">Still have questions?
                </h3>
                <p style="color: var(--text-body); font-size: 0.92rem; margin-bottom: 16px;">Our automation engineers
                    are ready to assist you with custom configurations and enterprise setups.</p>
                <a href="/#inquiry" class="btn-primary" style="display: inline-flex;">📩 Contact Support & Sales</a>
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p>© <?php echo date('Y'); ?> ChatModel.in — Enterprise Conversational AI & Multi-Tenant Automation
                    Platform. All Rights Reserved.</p>
                <div
                    style="margin-top: 10px; font-size: 0.84rem; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <a href="/faq.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">FAQ</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/privacy.php"
                        style="color: var(--text-body); text-decoration: none; transition: color 0.2s;"
                        onmouseover="this.style.color='var(--primary)'"
                        onmouseout="this.style.color='var(--text-body)'">Privacy Policy</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/terms.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;"
                        onmouseover="this.style.color='var(--primary)'"
                        onmouseout="this.style.color='var(--text-body)'">Terms & Conditions</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/login.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;"
                        onmouseover="this.style.color='var(--primary)'"
                        onmouseout="this.style.color='var(--text-body)'">Client Portal</a>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Day/Night Theme Management
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

        // Accordion Toggle Function
        function toggleFaq(el) {
            const item = el.closest('.faq-item');
            if (item) {
                item.classList.toggle('active');
            }
        }

        // FAQ Real-time Search Filter
        function filterFaq() {
            const query = document.getElementById('faqSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.faq-item');
            const categories = document.querySelectorAll('.faq-category');

            items.forEach(item => {
                const q = item.querySelector('.faq-question span:first-child').textContent.toLowerCase();
                const a = item.querySelector('.faq-answer').textContent.toLowerCase();
                if (q.includes(query) || a.includes(query)) {
                    item.style.display = 'block';
                    if (query.length > 1) {
                        item.classList.add('active');
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide category headers based on visible items
            categories.forEach(cat => {
                const visibleItems = cat.querySelectorAll('.faq-item[style="display: block;"], .faq-item:not([style*="display: none"])');
                const hasVisible = Array.from(cat.querySelectorAll('.faq-item')).some(i => i.style.display !== 'none');
                cat.style.display = hasVisible ? 'block' : 'none';
            });
        }
    </script>
    <script src="/assets/js/webmcp.js"></script>
</body>

</html>