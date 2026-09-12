<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($businessName); ?> - AI Assistant</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
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
                    <div class="status-tag">
                        <span class="status-dot"></span>
                        <span id="headerStatusText"><?php echo $isChatAuthenticated ? 'Internal Team Session' : 'AI Concierge Online'; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <?php if ($chatAccessMode === 'private' && $isChatAuthenticated): ?>
                    <span class="auth-status-btn active" title="Authenticated for Internal Use">🔒 Team</span>
                    <button onclick="handleChatLogout()" class="auth-status-btn" title="Exit internal session">Log out</button>
                <?php endif; ?>

                <!-- Day / Night Mode Toggle Button -->
                <button id="themeToggleBtn" class="theme-toggle-btn" title="Toggle Day / Night Mode" aria-label="Toggle theme">
                    <span id="themeIcon">☀️</span>
                </button>
            </div>
        </div>

        <?php if ($chatAccessMode === 'private' && !$isChatAuthenticated): ?>
            <!-- PRIVATE MODE: AUTHENTICATION LOCK GATE -->
            <div class="auth-gate-container" id="authGate">
                <div class="auth-card">
                    <div class="auth-card-icon">🔒</div>
                    <div class="auth-card-title">Internal AI Assistant</div>
                    <div class="auth-card-desc">
                        This assistant is restricted for internal use. Please enter your workspace passcode to start chatting.
                    </div>
                    <form id="chatLoginForm" onsubmit="handleChatLoginSubmit(event)">
                        <div class="auth-input-group">
                            <label for="chatPasswordInput">Team Passcode or Password</label>
                            <input type="password" id="chatPasswordInput" class="auth-input" placeholder="Enter access passcode..." required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn-unlock" id="unlockBtn">
                            <span>Unlock AI Assistant</span>
                            <span>➔</span>
                        </button>
                        <div id="authErrorMsg" class="auth-error-msg"></div>
                    </form>
                </div>
            </div>

            <!-- CHAT MESSAGES & INPUT (Hidden until authenticated) -->
            <div class="chat-messages" id="chatMessages" style="display: none;">
                <div class="message bot">
                    <p><?php echo htmlspecialchars($welcomeMessage); ?></p>
                </div>
                <div class="loading-indicator" id="loadingIndicator">
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                    <div class="loading-dot"></div>
                </div>
            </div>

            <div class="chat-input-container" id="chatInputContainer" style="display: none;">
                <input type="text" id="chatInput" class="chat-input" placeholder="Type your query..." autocomplete="off">
                <button id="sendBtn" class="send-button" aria-label="Send message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        <?php else: ?>
            <!-- PUBLIC OR AUTHENTICATED CHAT VIEW -->
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

            <div class="chat-input-container" id="chatInputContainer">
                <input type="text" id="chatInput" class="chat-input" placeholder="Type your query..." autocomplete="off">
                <button id="sendBtn" class="send-button" aria-label="Send message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        <?php endif; ?>

        <div class="powered-by">
            <div style="font-size: 0.68rem; color: var(--text-muted); opacity: 0.85; margin-bottom: 3px;">
                ⚠️ AI can make mistakes. Please verify important info.
            </div>
            Powered by <a href="https://chatmodel.in" target="_blank" rel="noopener">ChatModel Enterprise Engine</a>
        </div>
    </div>

    <script>
        window.TENANT_SUBDOMAIN = "<?php echo htmlspecialchars($subdomain, ENT_QUOTES); ?>";
        window.CHAT_ACCESS_MODE = "<?php echo htmlspecialchars($chatAccessMode, ENT_QUOTES); ?>";
        window.IS_CHAT_AUTHENTICATED = <?php echo $isChatAuthenticated ? 'true' : 'false'; ?>;
    </script>
    <script src="/assets/js/assistant.js"></script>
</body>
</html>
