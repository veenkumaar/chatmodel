# ChatModel — Multi-Tenant AI Automation SaaS Platform

A modern, high-performance multi-tenant AI chatbot platform powered by **PHP**, **SQLite**, and **n8n workflow automation**, designed for lightning-fast deployment on a single cloud VPS/Droplet with dynamic wildcard subdomain routing and comprehensive **AI Agent Discovery Standards**.

---

## 📁 Project Architecture

```text
chatmodel/
├── .well-known/
│   ├── agent-skills/
│   │   ├── chatmodel-assistant/
│   │   │   └── SKILL.md            # Agent Skill artifact
│   │   └── index.json              # Agent Skills Discovery RFC v0.2.0 index
│   ├── mcp/
│   │   └── server-card.json        # SEP-1649 MCP Server Card
│   ├── ai-catalog.json             # ARD (Agentic Resource Discovery) capability manifest
│   ├── api-catalog                 # RFC 9727 / RFC 9264 API Catalog linkset
│   ├── api-catalog.json            # API Catalog duplicate
│   ├── openid-configuration        # OpenID Connect 1.0 Discovery metadata
│   ├── oauth-authorization-server  # RFC 8414 OAuth 2.0 Server metadata (with agent_auth)
│   ├── oauth-protected-resource    # RFC 9728 OAuth Protected Resource metadata
│   └── jwks.json                   # Public JSON Web Key Set
├── assets/
│   ├── css/
│   │   ├── landing.css             # SaaS landing page styles, responsive grid, Day/Night themes
│   │   ├── assistant.css           # Multi-tenant AI chatbot UI, mobile layout, bubble chat
│   │   └── admin.css               # Admin console & Client workspace dashboard styles
│   └── js/
│       ├── landing.js              # Interactive live simulator, inquiry submission, theme toggle
│       ├── assistant.js            # AI chat proxy connection, SSE streaming, Markdown, quick replies
│       ├── admin.js                # Tenant CRUD, live search, logs modal, DNS verification
│       ├── workspace.js            # Client workspace portal settings, CRM tester, live log viewer
│       ├── webmcp.js               # WebMCP browser tool registration for AI agents
│       └── auth.js                 # Authentication theme toggle
├── db/
│   ├── database.php                # SQLite Database singleton, query repository & migrations
│   └── security.php                # Security hardening, CSRF, rate limiting, Markdown negotiation
├── api/
│   ├── agent/
│   │   ├── register.php            # Programmatic agent registration endpoint (Auth.md)
│   │   ├── claim.php               # Identity assertion verification endpoint
│   │   └── revoke.php              # Access token revocation endpoint
│   ├── chat.php                    # Multi-tenant AI chat proxy, SSE streaming & webhook dispatcher
│   ├── health.php                  # API health status check
│   ├── mcp.php                     # Streamable Model Context Protocol (MCP) server
│   ├── openapi.json                # OpenAPI 3.0 specification for all endpoints
│   ├── tenants.php                 # JSON REST API for tenant provisioning & quotas
│   └── token.php                   # OAuth 2.0 token issuance endpoint
├── views/
│   ├── errors/
│   │   ├── 404.php                 # Tenant workspace not found page (with noindex meta)
│   │   └── 403.php                 # Tenant disabled / maintenance screen (with noindex meta)
│   ├── landing/
│   │   └── index.php               # Marketing landing page & live simulator view
│   ├── assistant/
│   │   └── chat_interface.php      # Dedicated client AI chatbot interface
│   ├── auth/
│   │   └── login_form.php          # Unified workspace & admin login form
│   ├── admin/
│   │   └── dashboard.php           # Global Admin control console
│   └── tenant/
│       └── workspace.php           # Tenant self-service client dashboard
├── deploy/
│   ├── PRODUCTION_SETUP.md         # Production server & DNS setup guide (with DNS-AID & DNSSEC)
│   ├── deploy.sh                   # Automated 1-command deployment script
│   └── nginx-wildcard.conf         # Hardened Nginx configuration with .well-known routes
├── data/
│   └── tenants.db                  # Embedded SQLite database (WAL mode)
├── auth.md                         # Auth.md agent authentication & registration guide
├── faq.php                         # Comprehensive platform FAQ & technical architecture
├── index.php                       # Entrypoint, discovery router & dynamic subdomain router
├── llms.txt                        # Standard llmstxt.org AI index file
├── login.php                       # Authentication controller & portal dispatcher
├── privacy.php                     # Privacy policy & data protection standards
├── robots.txt                      # Search rules, Content-Signals, and Agentmap
├── sitemap.xml                     # Public XML sitemap (isolated from tenant subdomains)
├── terms.php                       # Terms & Conditions of service
└── .htaccess                       # Hardened Apache URL rewrites & security rules
```

---

## 🤖 AI Agent Discovery & Protocol Standards

ChatModel implements modern discovery, negotiation, and integration specifications for autonomous AI agents:

| Specification | Endpoint / Configuration | Description |
| :--- | :--- | :--- |
| **Link Response Headers** | `RFC 8288` / `RFC 9727` | Advertises API catalog, OpenAPI specs, and docs on root responses |
| **Markdown Negotiation** | `Accept: text/markdown` | Returns rich Markdown with `x-markdown-tokens` for LLMs across all pages |
| **API Catalog** | `/.well-known/api-catalog` | RFC 9727 / RFC 9264 `application/linkset+json` resource index |
| **OpenAPI 3.0** | `/api/openapi.json` | Complete machine-readable API definition |
| **OAuth 2.0 / OIDC Discovery** | `/.well-known/openid-configuration` & `/.well-known/oauth-authorization-server` | Authorization, token, and JWKS endpoints with `agent_auth` block |
| **OAuth Protected Resource** | `/.well-known/oauth-protected-resource` | RFC 9728 resource metadata and supported authorization servers |
| **Auth.md** | `/auth.md` | WorkOS / RFC 9728 agent registration and token provisioning spec |
| **MCP Server Card** | `/.well-known/mcp/server-card.json` | SEP-1649 Model Context Protocol server definition |
| **Agent Skills Discovery** | `/.well-known/agent-skills/index.json` | Cloudflare RFC v0.2.0 discovery index with SHA-256 digested `SKILL.md` |
| **WebMCP API** | `assets/js/webmcp.js` | Browser-level tool exposure via `navigator.modelContext` |
| **ARD Manifest** | `/.well-known/ai-catalog.json` | Agentic Resource Discovery capability manifest with `urn:air` IDs |
| **Content Signals** | `robots.txt` | Declares `ai-train=no, search=yes, ai-input=yes` preference directives |
| **DNS-AID** | `_index._agents` / `_a2a._agents` | DNS-based agent discovery via RFC 9460 SVCB/HTTPS records with DNSSEC |

---

## 🛡️ Multi-Tenant Security & Anti-Indexing

1. **Subdomain Anti-Indexing Protection**:
   - `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet` emitted for all tenant subdomains.
   - HTML `<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">` in all client and error views.
   - [sitemap.xml](sitemap.xml) strictly indexes public pages and never leaks customer subdomains.
   - [robots.txt](robots.txt) blocks search parameter queries (`Disallow: /*?subdomain=*`).

2. **Customer Quota & Usage Protection**:
   - **Monthly Conversation Limits**: Automatically caps messages per tenant based on tier (Starter: 1,000, Pro: 10,000, Enterprise: Custom). Requests exceeding quota receive `429 Too Many Requests`.
   - **IP Rate Limiting**: Capped at 40 requests/min per IP to prevent flood attacks.
   - **Access Modes**: Supports public, password-protected private, or internal access modes.
   - **Prompt Length Guard**: 3,000-character payload cap.

---

## 🛠️ Server Setup & Deployment

### 1. Automated Setup (Ubuntu / Debian)
```bash
cd /var/www/html/chatmodel
sudo bash deploy/deploy.sh
```

### 2. Manual Permissions & SQLite
```bash
sudo chown -R www-data:www-data /var/www/html/chatmodel
sudo chmod -R 775 /var/www/html/chatmodel/data
```

### 3. Nginx Setup
```bash
sudo cp deploy/nginx-wildcard.conf /etc/nginx/sites-available/chatmodel
sudo ln -s /etc/nginx/sites-available/chatmodel /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 4. Wildcard SSL Certificate (Certbot)
```bash
sudo certbot certonly --manual --preferred-challenges dns -d "chatmodel.in" -d "*.chatmodel.in"
```

---

## 📡 Core API Reference

- **Chat Assistant**: `POST /api/chat.php`
  ```json
  {
    "message": "What services do you offer?",
    "subdomain": "demo",
    "sessionId": "session_123"
  }
  ```
- **Health Check**: `GET /api/health.php`
- **MCP Server**: `GET /api/mcp.php` / `POST /api/mcp.php`
- **Agent Registration**: `POST /api/agent/register.php`
- **OAuth Token**: `POST /api/token.php`
- **Tenant Management**: `GET /api/tenants.php` & `POST /api/tenants.php`
