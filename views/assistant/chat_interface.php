<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($businessName); ?> - AI Assistant</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/assistant.css">
    <style>
        :root {
            --tenant-color: <?php echo htmlspecialchars($themeColor); ?>;
        }
        .message.user {
            background: <?php echo htmlspecialchars($themeColor); ?> !important;
            box-shadow: 0 4px 15px <?php echo htmlspecialchars($themeColor); ?>44 !important;
        }
        .send-button {
            background: <?php echo htmlspecialchars($themeColor); ?> !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="header-info">
                <div class="header-avatar">⚡</div>
                <div>
                    <div class="header-title"><?php echo htmlspecialchars($businessName); ?></div>
                    <div class="status-tag"><span class="status-dot"></span> AI Concierge Online</div>
                </div>
            </div>
            <!-- Day / Night Mode Toggle Button -->
            <button id="themeToggleBtn" class="theme-toggle-btn" title="Toggle Day / Night Mode" aria-label="Toggle theme">
                <span id="themeIcon">☀️</span>
            </button>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                <p><?php echo htmlspecialchars($welcomeMessage); ?></p>
            </div>
            <div class="loading-indicator" id="loadingIndicator">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>

        <div class="chat-input-container">
            <input type="text" id="chatInput" class="chat-input" placeholder="Type your query..." autocomplete="off">
            <button id="sendBtn" class="send-button" aria-label="Send message">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
        <div class="powered-by">
            Powered by <a href="https://chatmodel.in" target="_blank" rel="noopener">ChatModel Enterprise Engine</a>
        </div>
    </div>

    <script>
        window.TENANT_SUBDOMAIN = "<?php echo htmlspecialchars($subdomain, ENT_QUOTES); ?>";
    </script>
    <script src="/assets/js/assistant.js"></script>
</body>
</html>
