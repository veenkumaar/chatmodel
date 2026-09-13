/**
 * WebMCP - Browser Model Context Protocol (SEP / WebML EPP)
 * Exposes client tools to browser AI agents via navigator.modelContext
 */
(function() {
    'use strict';

    const tools = [
        {
            name: "send_chat_message",
            description: "Send a conversational message or customer inquiry to the ChatModel 24/7 AI assistant",
            inputSchema: {
                type: "object",
                properties: {
                    message: {
                        type: "string",
                        description: "The prompt or inquiry message to send to the assistant"
                    },
                    subdomain: {
                        type: "string",
                        description: "Optional assistant subdomain (default: 'demo')",
                        default: "demo"
                    }
                },
                required: ["message"]
            },
            execute: async ({ message, subdomain = 'demo' }) => {
                try {
                    const res = await fetch('/api/chat.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            message: message,
                            subdomain: subdomain,
                            sessionId: 'webmcp_' + Math.random().toString(36).substring(2, 9)
                        })
                    });
                    const data = await res.json();
                    return data;
                } catch (err) {
                    return { error: err.message };
                }
            }
        },
        {
            name: "get_pricing_plans",
            description: "Retrieve available ChatModel pricing tiers, features, and subscription limits",
            inputSchema: {
                type: "object",
                properties: {}
            },
            execute: async () => {
                return {
                    currency: "INR",
                    plans: [
                        {
                            name: "Starter",
                            price: "₹1,999/mo",
                            features: ["1 AI Assistant", "Dedicated Subdomain", "1,000 Chats/mo", "Webhook Automation"]
                        },
                        {
                            name: "Professional",
                            price: "₹4,999/mo",
                            features: ["3 AI Assistants", "Custom Branding", "10,000 Chats/mo", "Live Streaming SSE", "Priority Support"]
                        },
                        {
                            name: "Agency & Enterprise",
                            price: "₹9,999/mo",
                            features: ["Unlimited Assistants", "Whitelabel CNAME Domains", "Unlimited Chats", "Direct CRM & Database Webhooks"]
                        }
                    ]
                };
            }
        },
        {
            name: "get_faq_answers",
            description: "Query ChatModel platform FAQ, technical capabilities, webhooks, and security",
            inputSchema: {
                type: "object",
                properties: {
                    topic: {
                        type: "string",
                        description: "Topic or keyword (e.g., 'subdomains', 'webhooks', 'security', 'pricing')"
                    }
                }
            },
            execute: async ({ topic = '' }) => {
                try {
                    const res = await fetch('/faq.php', {
                        headers: { 'Accept': 'text/markdown' }
                    });
                    const markdown = await res.text();
                    return { content: markdown };
                } catch (err) {
                    return { error: err.message };
                }
            }
        }
    ];

    function initWebMCP() {
        const mc = (typeof navigator !== 'undefined' && navigator.modelContext) ? navigator.modelContext :
                   (typeof window !== 'undefined' && window.modelContext) ? window.modelContext : null;

        if (mc) {
            // Provide Context (Standard ProvideContext API)
            if (typeof mc.provideContext === 'function') {
                try {
                    mc.provideContext({ tools: tools });
                } catch (e) {
                    console.warn('[WebMCP] provideContext failed:', e);
                }
            }

            // Register Tool (Standard RegisterTool API)
            if (typeof mc.registerTool === 'function') {
                tools.forEach(tool => {
                    try {
                        mc.registerTool(tool);
                    } catch (e) {
                        console.warn(`[WebMCP] registerTool (${tool.name}) failed:`, e);
                    }
                });
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWebMCP);
    } else {
        initWebMCP();
    }
})();
