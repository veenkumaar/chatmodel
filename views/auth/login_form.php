<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ChatModel Workspace</title>
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

        <div class="logo-badge">⚡</div>
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
                <input type="text" id="username" name="username" class="form-control"
                    placeholder="e.g. aditya, demo, or admin" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
                    required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Authenticate & Enter Portal</button>
        </form>
    </div>

    <script src="/assets/js/auth.js"></script>
</body>

</html>