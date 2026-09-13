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
    <title>Privacy Policy - ChatModel.in</title>
    <meta name="description" content="ChatModel.in Privacy Policy. Learn how we handle customer data, conversation privacy, SSL encryption, and multi-tenant security.">
    <link rel="canonical" href="https://chatmodel.in/privacy.php">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/landing.css">
    <style>
        .legal-wrapper {
            max-width: 860px;
            margin: 40px auto 80px auto;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.05);
        }
        .legal-header {
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .legal-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 8px;
        }
        .legal-meta {
            font-size: 0.88rem;
            color: var(--text-body);
        }
        .legal-content h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-heading);
            margin: 28px 0 12px 0;
        }
        .legal-content p, .legal-content li {
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--text-body);
            margin-bottom: 14px;
        }
        .legal-content ul {
            margin-left: 24px;
            margin-bottom: 18px;
        }
        .legal-content strong {
            color: var(--text-heading);
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
        @media (max-width: 768px) {
            .legal-wrapper {
                padding: 28px 20px;
                margin: 20px auto 60px auto;
            }
            .legal-header h1 {
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
                <button id="mainThemeBtn" class="theme-switch-btn" title="Toggle Day / Night Mode" aria-label="Toggle theme">
                    <span id="mainThemeIcon">☀️</span>
                </button>
            </div>
        </nav>

        <main class="legal-wrapper">
            <a href="/" class="back-nav">← Back to ChatModel Home</a>

            <div class="legal-header">
                <div class="badge-pill" style="margin-bottom: 12px;">🛡️ Data Protection & Privacy</div>
                <h1>Privacy Policy</h1>
                <div class="legal-meta">Last Updated: September 12, 2026 | Effective Date: September 12, 2026</div>
            </div>

            <div class="legal-content">
                <p>
                    Welcome to <strong>ChatModel.in</strong> ("ChatModel", "we", "our", or "us"). We provide an enterprise-grade multi-tenant conversational AI platform enabling businesses to deploy dedicated 24/7 AI assistants on isolated subdomains. We take data protection, client privacy, and enterprise confidentiality extremely seriously.
                </p>

                <h2>1. Information We Collect</h2>
                <p>We only collect information necessary to operate, secure, and improve our services:</p>
                <ul>
                    <li><strong>Account & Workspace Data:</strong> When administrators provision a workspace, we store the business name, assigned subdomain slug, brand configurations, theme accents, and hashed authentication credentials.</li>
                    <li><strong>Inquiry & Lead Information:</strong> When prospective clients submit inquiries via our public portal, we capture contact details (name, email, phone, company, and project requirements) to fulfill consultations.</li>
                    <li><strong>Conversation Logs:</strong> Depending on the workspace subscription tier (such as Enterprise), customer chat sessions may be stored in isolated tenant databases for analytics and customer support review.</li>
                    <li><strong>Technical Telemetry:</strong> Standard non-identifying server logs, including IP addresses, timestamps, and request headers to safeguard system availability, prevent DDoS attacks, and enforce rate limits.</li>
                </ul>

                <h2>2. How We Use Information</h2>
                <p>Information gathered is utilized strictly for:</p>
                <ul>
                    <li>Provisioning and maintaining dedicated client workspace subdomains (e.g., <code>client.chatmodel.in</code>).</li>
                    <li>Dispatching real-time HTTPS webhook requests to configured AI workflows (such as n8n, Make, Flowise, or internal client APIs).</li>
                    <li>Monitoring conversation quota thresholds according to subscription plan tiers.</li>
                    <li>Securing tenant portals against unauthorized access using CSRF defense, rate-limiting, and hashed credentials.</li>
                </ul>

                <h2>3. Multi-Tenant Data Isolation & Security</h2>
                <p>
                    Every client workspace operates under strict isolation boundaries. We enforce:
                </p>
                <ul>
                    <li><strong>No-Index Search Engine Exclusion:</strong> Client workspaces and conversation records are guarded with <code>noindex</code>, <code>nofollow</code>, and <code>X-Robots-Tag</code> directives to ensure search engines never scrape or index private client interactions.</li>
                    <li><strong>Access Control Modes:</strong> Workspaces can be switched between Public (unauthenticated) and Private (passcode/credential gated) modes to prevent unauthorized conversation access.</li>
                    <li><strong>Automated SSL/TLS Encryption:</strong> All traffic in transit is encrypted using modern 256-bit TLS protocols.</li>
                </ul>

                <h2>4. Third-Party Webhooks & Integrations</h2>
                <p>
                    ChatModel dispatches user prompts to client-defined webhook endpoints. We do not sell, rent, or monetize conversation transcripts. Clients maintain full ownership and control over their downstream data pipelines and AI model providers.
                </p>

                <h2>5. Cookies & Local Storage</h2>
                <p>
                    We use localized session cookies and browser storage strictly for essential operations, such as keeping administrators logged into the portal, validating CSRF security tokens, and remembering Day/Night visual theme preferences. We do not use third-party cross-site advertising cookies.
                </p>

                <h2>6. Data Retention & Deletion</h2>
                <p>
                    Administrators have the right to request full erasure of their workspace and associated session history at any time. When an administrator deletes a tenant workspace from the Admin Console, all associated database records and session logs are permanently expunged.
                </p>

                <h2>7. AI-Generated Output & Non-Liability Disclaimer</h2>
                <p>
                    <strong>Artificial Intelligence can make mistakes, hallucinate, or generate inaccurate responses.</strong> ChatModel provides software infrastructure and orchestration bridges to third-party artificial intelligence engines and client-configured automation webhooks. 
                </p>
                <p>
                    ChatModel, its founders, and operators <strong>do not assume any legal responsibility or liability</strong> for any incorrect statements, erroneous guidance, commitments, pricing quotes, or actions generated by the AI assistant. Users and clients must verify all critical, financial, legal, or medical information independently.
                </p>

                <h2>8. Contact & Data Protection Inquiries</h2>
                <p>
                    For any questions regarding our Privacy Policy or data handling practices, please contact us via the <a href="/#inquiry" style="color: var(--primary); font-weight: 600;">Inquiry Portal</a> or email us at <strong>privacy@chatmodel.in</strong>.
                </p>
            </div>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p>© <?php echo date('Y'); ?> ChatModel.in — Enterprise Conversational AI & Multi-Tenant Automation Platform. All Rights Reserved.</p>
                <div style="margin-top: 10px; font-size: 0.84rem; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <a href="/faq.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">FAQ</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/privacy.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Privacy Policy</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/terms.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">Terms & Conditions</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/login.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">Client Portal</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="/assets/js/landing.js"></script>
</body>

</html>
