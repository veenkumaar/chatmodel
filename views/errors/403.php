<!DOCTYPE html>
<html lang="en">
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
    <title><?php echo htmlspecialchars($businessName ?? 'Assistant'); ?> - Service Temporarily Paused</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #090d16;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 24px;
            padding: 48px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }
        .icon-badge {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px auto;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }
        h1 {
            color: #f8fafc;
            font-size: 1.6rem;
            margin-bottom: 12px;
        }
        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 24px;
            font-size: 1rem;
        }
        .sub-info {
            background: rgba(15, 23, 42, 0.8);
            border: 1px dashed #334155;
            border-radius: 12px;
            padding: 12px;
            font-size: 0.85rem;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-badge">⏸️</div>
        <h1><?php echo htmlspecialchars($businessName ?? 'AI Assistant'); ?></h1>
        <p>The AI Concierge for <strong><?php echo htmlspecialchars($subdomain ?? ''); ?>.chatmodel.in</strong> is currently offline or undergoing scheduled maintenance.</p>
        <div class="sub-info">
            Please contact support or check back shortly.
        </div>
    </div>
</body>
</html>
