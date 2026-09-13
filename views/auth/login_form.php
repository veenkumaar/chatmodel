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
    <title>Login - ChatModel Workspace</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>
    <div class="ambient-glow"></div>

    <div class="login-card auth-layout">
        <!-- Theme Toggle Button -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
            <button id="adminThemeBtn" class="theme-switch-btn" title="Toggle Day / Night Mode"
                aria-label="Toggle theme">
                <span id="adminThemeIcon">☀️</span>
            </button>
        </div>

        <a href="/" style="text-decoration: none;" title="Back to ChatModel Home">
            <div class="logo-badge">⚡</div>
        </a>
        <h1>Workspace & Admin Login</h1>
        <p class="subtitle">Access your dedicated assistant workspace or admin console</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login.php">
            <input type="hidden" name="login_submit" value="1">
            <input type="hidden" name="csrf_token" value="<?php echo Security::getCsrfToken(); ?>">
            <div class="form-group">
                <label for="username">Username or Subdomain Slug</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. username"
                    required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="password"
                    required autocomplete="current-password">
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: -6px; margin-bottom: 14px;">
                <a href="javascript:void(0)" onclick="openForgotHelpModal()"
                    style="font-size: 0.78rem; color: var(--primary); text-decoration: none; font-weight: 600;">
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn-submit">Authenticate & Enter Portal</button>
        </form>

        <!-- Forgot Password Help Modal -->
        <div id="forgotHelpModal" class="modal-overlay">
            <div class="modal-box" style="max-width: 440px; text-align: center;">
                <div style="font-size: 2.2rem; margin-bottom: 10px;">🔐</div>
                <div class="modal-title" style="margin-bottom: 8px;">Forgot Subdomain Password?</div>
                <p style="color: var(--text-body); font-size: 0.86rem; line-height: 1.5; margin-bottom: 18px;">
                    Workspace passwords for dedicated subdomains are managed and secured by your system administrator.
                    Please contact your <strong>Super Admin</strong> to trigger a secure password reset from the Admin
                    Console.
                </p>
                <button type="button" onclick="closeForgotHelpModal()" class="btn-submit"
                    style="margin-top: 0; padding: 10px;">
                    Got it, Close
                </button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/"
                style="color: var(--text-body); text-decoration: none; font-size: 0.88rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s ease;"
                onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-body)'">
                <span>←</span> <span>Back to ChatModel Home</span>
            </a>
        </div>
    </div>

    <script src="/assets/js/auth.js"></script>
</body>

</html>