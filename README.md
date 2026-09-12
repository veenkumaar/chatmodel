# ChatModel — Multi-Tenant AI Automation SaaS Platform

A modern, multi-tenant AI chatbot platform powered by **PHP**, **SQLite**, and **n8n workflow automation**, designed for fast deployment on a single DigitalOcean droplet with dynamic subdomain routing.

---

## 📁 Structured Project Architecture

```text
chatmodel/
├── assets/
│   ├── css/
│   │   ├── landing.css          # SaaS landing page styles, responsive grid, Day/Night themes
│   │   ├── assistant.css        # Multi-tenant AI chatbot UI, mobile layout, bubble chat
│   │   └── admin.css            # Admin console & Client workspace dashboard styles
│   └── js/
│       ├── landing.js          # Interactive live simulator, inquiry submission, theme toggle
│       ├── assistant.js        # AI chat proxy connection, Markdown parsing, sound & quick replies
│       ├── admin.js            # Tenant CRUD, live search, logs modal, DNS verification
│       ├── workspace.js        # Client workspace portal settings, CRM tester, live log viewer
│       └── auth.js             # Authentication theme toggle
├── db/
│   ├── database.php            # SQLite Database singleton, query repository & migrations
│   └── security.php            # Security hardening, CSRF, rate limiting, and sanitization
├── api/
│   ├── chat.php                # Multi-tenant AI chat proxy & webhook dispatcher
│   └── tenants.php             # JSON REST API for tenant provisioning & management
├── views/
│   ├── errors/
│   │   ├── 404.php             # Tenant workspace not found page
│   │   └── 403.php             # Tenant disabled / maintenance screen
│   ├── landing/
│   │   └── index.php           # Marketing landing page & live simulator view
│   ├── assistant/
│   │   └── chat_interface.php  # Dedicated client AI chatbot interface
│   ├── auth/
│   │   └── login_form.php      # Unified workspace & admin login form
│   ├── admin/
│   │   └── dashboard.php       # Global Admin control console
│   └── tenant/
│       └── workspace.php       # Tenant self-service client dashboard
├── deploy/
│   ├── PRODUCTION_SETUP.md     # Production DigitalOcean deployment guide
│   ├── deploy.sh               # Automated deployment script
│   └── nginx-wildcard.conf     # Nginx wildcard subdomain configuration
├── data/
│   └── tenants.db              # Embedded SQLite database
├── index.php                   # Entrypoint & dynamic subdomain router
├── login.php                   # Authentication controller & portal dispatcher
├── README.md                   # Project documentation
└── .htaccess                   # Apache URL rewrites & security rules
```

---

## 🌐 Endpoint Architecture

| Endpoint | Destination | Description |
| :--- | :--- | :--- |
| `https://n8n.chatmodel.in` | Docker / Node Service (:5678) | Automation workflow builder and webhook receiver |
| `https://chatmodel.in` | `/var/www/html/chatmodel/index.php` | Primary SaaS landing page, automation reply benefits & tenant management |
| `https://aditya.chatmodel.in` | Dynamic Multi-Tenant Subdomain | Branded client AI assistant with enable/disable switch & custom theme |

---

## 🚀 Key Features

1. **Automated Business Reply Engine**:
   - 24/7 lead qualification, instant response time, and automated CRM triggers via n8n.
2. **Dynamic Multi-Subdomain Multi-Tenancy**:
   - Any client subdomain (e.g., `aditya.chatmodel.in`, `acme.chatmodel.in`) is automatically routed without manual DNS/Nginx changes per client.
3. **SQLite Database Architecture**:
   - Zero-configuration embedded SQLite database (`data/tenants.db`) with automatic table migrations and chat logging.
4. **Service Enable / Disable Control**:
   - Instantly pause or resume individual client subdomains directly from the administrative portal. Inactive tenants display a maintenance screen.
5. **Interactive Live Demo**:
   - Test automated AI replies in real-time on the landing page.

---

## 🛠️ Server Setup on DigitalOcean

### 1. File Placement
Place the project repository in your webroot:
```bash
cd /var/www/html/chatmodel
# Ensure write permissions for SQLite database storage
sudo chown -R www-data:www-data /var/www/html/chatmodel
sudo chmod -R 775 /var/www/html/chatmodel/data
```

### 2. Nginx Configuration
Copy the configuration from `deploy/nginx-wildcard.conf` to `/etc/nginx/sites-available/chatmodel`:
```bash
sudo cp deploy/nginx-wildcard.conf /etc/nginx/sites-available/chatmodel
sudo ln -s /etc/nginx/sites-available/chatmodel /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 3. Wildcard SSL Certificate (Let's Encrypt / Certbot)
To support all dynamic subdomains (`*.chatmodel.in`) with SSL:
```bash
sudo certbot certonly --manual --preferred-challenges dns -d "chatmodel.in" -d "*.chatmodel.in"
```

---

## 📡 API Endpoints

- `GET /api/tenants.php` — Retrieve list of all registered tenants.
- `POST /api/tenants.php` — Provision a new tenant or toggle enable/disable status:
  ```json
  {
    "action": "toggle_status",
    "subdomain": "aditya",
    "is_active": 1
  }
  ```
- `POST /api/chat.php` — Multi-tenant chat proxy forwarding queries to client's assigned n8n webhook:
  ```json
  {
    "message": "What services do you offer?",
    "subdomain": "aditya",
    "sessionId": "session_123"
  }
  ```
