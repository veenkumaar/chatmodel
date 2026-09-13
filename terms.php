<?php
require_once __DIR__ . '/db/security.php';
if (Security::wantsMarkdown()) {
    $md = <<<MD
# Terms & Conditions of Service - ChatModel

**Effective Date:** January 1, 2025  
**Last Updated:** September 2026  

Welcome to **ChatModel** (https://chatmodel.in). By accessing or using our SaaS platform, API endpoints, or client workspaces, you agree to be bound by these Terms & Conditions.

## 1. Service Description
ChatModel provides multi-tenant conversational AI infrastructure, dynamic client workspaces, API endpoints, and webhook routing tools for commercial enterprises.

## 2. Acceptable Use
You agree not to:
- Use the platform to generate or distribute malicious, deceptive, fraudulent, or harmful content.
- Attempt to bypass tenant rate limits, security defenses, or access unauthorized tenant databases.
- Launch denial of service (DoS/DDoS) attacks against ChatModel infrastructure.

## 3. Service Level Agreement & Availability
ChatModel targets 99.9% platform availability. Maintenance windows and critical updates are communicated to workspace administrators.

## 4. Subscriptions & Billing
Subscriptions are billed on a recurring monthly or annual basis as detailed on our pricing schedule.

## 5. Contact & Inquiries
For legal or billing inquiries, contact `support@chatmodel.in`.
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
    <title>Terms & Conditions - ChatModel.in</title>
    <meta name="description" content="ChatModel.in Terms & Conditions of Service. Review our service level commitments, acceptable use policies, and subscription terms.">
    <link rel="canonical" href="https://chatmodel.in/terms.php">
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
                <div class="badge-pill" style="margin-bottom: 12px;">⚖️ Terms of Service</div>
                <h1>Terms & Conditions</h1>
                <div class="legal-meta">Last Updated: September 12, 2026 | Effective Date: September 12, 2026</div>
            </div>

            <div class="legal-content">
                <p>
                    These Terms and Conditions ("Terms") govern your access to and use of the <strong>ChatModel.in</strong> software-as-a-service platform ("Platform", "Service", or "ChatModel"). By registering, provisioning a workspace, or using our services, you agree to be bound by these Terms.
                </p>

                <h2>1. Service Description</h2>
                <p>
                    ChatModel provides enterprise conversational AI software, multi-tenant workspace provisioning, automated SSL-secured subdomains, conversational telemetry, and webhook orchestration bridges for businesses and agencies.
                </p>

                <h2>2. Workspace Accounts & Security</h2>
                <p>
                    When an administrator provisions a dedicated workspace (e.g., <code>yourbrand.chatmodel.in</code>):
                </p>
                <ul>
                    <li>You are responsible for maintaining the confidentiality of your workspace password and internal access passcodes.</li>
                    <li>You are solely responsible for all activities and webhook workflows configured under your dedicated subdomain.</li>
                    <li>You agree to immediately notify ChatModel of any unauthorized security breach or credential compromise.</li>
                </ul>

                <h2>3. Acceptable Use Policy</h2>
                <p>You agree not to use ChatModel to:</p>
                <ul>
                    <li>Deploy malicious, harmful, abusive, fraudulent, or deceptive automation workflows.</li>
                    <li>Transmit spam, unsolicited mass promotions, or violate applicable anti-telemarketing regulations.</li>
                    <li>Attempt to breach, reverse engineer, probe, or compromise multi-tenant database isolation boundaries.</li>
                    <li>Infringe upon third-party intellectual property, privacy, or trade secret rights.</li>
                </ul>

                <h2>4. Subscription Plans, Quotas & Billing</h2>
                <p>
                    ChatModel offers multiple subscription tiers (Starter, Professional, and Enterprise):
                </p>
                <ul>
                    <li><strong>Monthly Conversation Quotas:</strong> Each subscription tier includes an allocated number of unique monthly conversations. If a workspace reaches its limit, the system gracefully advises upgrading to maintain 24/7 uptime.</li>
                    <li><strong>Service Kill-Switch:</strong> Administrators can temporarily toggle assistant active states or pause services at any time via the management dashboard.</li>
                    <li><strong>Plan Adjustments:</strong> Upgrades and tier adjustments take effect immediately upon administrative confirmation.</li>
                </ul>

                <h2>5. Webhook Availability & Third-Party Dependencies</h2>
                <p>
                    ChatModel dispatches visitor messages to external automation webhooks specified by the client. While ChatModel ensures high-availability server uptime for its platform, we are not liable for downtime, latency, or response inaccuracies originating from third-party webhook providers, custom servers, or external LLM APIs.
                </p>

                <h2>6. Intellectual Property Rights</h2>
                <p>
                    ChatModel retains all rights, title, and interest in and to the platform, UI design systems, codebase, and infrastructure. Clients retain full ownership and intellectual property of their brand assets, trademarks, custom prompt engineering, and proprietary business workflows.
                </p>

                <h2>7. AI Errors, Hallucinations & Non-Liability Disclaimer</h2>
                <p>
                    <strong>Generative AI models and automated assistants can make mistakes, produce inaccurate answers, or hallucinate.</strong> ChatModel operates as a software infrastructure provider and does not review or endorse individual automated responses generated by underlying AI engines or client webhook endpoints.
                </p>
                <p>
                    <strong>Under no circumstances shall ChatModel, its developers, or affiliates be held liable or responsible</strong> for any erroneous claims, misleading guidance, unauthorized promises, business losses, missed leads, or damages resulting from AI-generated outputs or actions taken by visitors based on assistant interactions. Clients and visitors use the AI assistant at their own discretion and risk.
                </p>

                <h2>8. Limitation of Liability</h2>
                <p>
                    To the maximum extent permitted by applicable law, ChatModel shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or business interruption arising out of the use or inability to use the platform.
                </p>

                <h2>9. Modifications to Terms</h2>
                <p>
                    We reserve the right to revise these Terms to reflect technological improvements or regulatory requirements. Continued use of the platform after updates constitutes acceptance of the modified Terms.
                </p>

                <h2>10. Governing Law & Contact</h2>
                <p>
                    These Terms shall be governed by and construed in accordance with the laws of India. For legal notices or contractual inquiries, contact us at <strong>legal@chatmodel.in</strong>.
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
                    <a href="/privacy.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">Privacy Policy</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/terms.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Terms & Conditions</a>
                    <span style="color: var(--card-border);">•</span>
                    <a href="/login.php" style="color: var(--text-body); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">Client Portal</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="/assets/js/landing.js"></script>
    <script src="/assets/js/webmcp.js"></script>
</body>

</html>
