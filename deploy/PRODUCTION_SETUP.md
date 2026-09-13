# 🚀 ChatModel SaaS - Complete Production Server Deployment Guide

This guide details everything required to host ChatModel live on your server (VPS, Cloud Server, Ubuntu/Debian, Apache, cPanel, or Nginx).

---

## 📋 System Requirements
- **OS**: Ubuntu 22.04/24.04 LTS, Debian 11/12, CentOS/AlmaLinux, or any standard Linux server.
- **Web Server**: Nginx or Apache / LiteSpeed with `mod_rewrite`.
- **PHP**: PHP 8.1, 8.2, 8.3, or 8.4
- **Required PHP Extensions**:
  - `php-sqlite3` (for high-speed multi-tenant database)
  - `php-curl` (for AI Automation webhooks and CRM pipelines)
  - `php-mbstring`
  - `php-xml`
  - `php-fpm` (for Nginx)

---

## 🌐 Step 1: DNS Setup (Crucial for Dedicated Subdomains)

To enable automatic dynamic subdomains (e.g. `aditya.chatmodel.in`, `demo.chatmodel.in`, `client.chatmodel.in`) without modifying DNS for each tenant:

1. Open your DNS provider (Cloudflare, GoDaddy, Namecheap, Route53, Hostinger).
2. Add the following **A Records**:

| Type | Name / Host | Value / Target | TTL |
| :--- | :--- | :--- | :--- |
| **A** | `@` (or `chatmodel.in`) | `YOUR_SERVER_PUBLIC_IP` | Auto / 300 |
| **A** | `www` | `YOUR_SERVER_PUBLIC_IP` | Auto / 300 |
| **A** | `*` *(Wildcard)* | `YOUR_SERVER_PUBLIC_IP` | Auto / 300 |

### 🤖 DNS for AI Discovery (DNS-AID) Records (RFC 9460 / DNS-AID)

To enable automated agent discovery via DNS:

1. Add the following **SVCB / HTTPS** discovery records in your DNS management panel:

| Type | Name / Host | Priority | Target | Value / Parameters |
| :--- | :--- | :--- | :--- | :--- |
| **SVCB** (or **HTTPS**) | `_index._agents` | `1` | `chatmodel.in.` | `alpn="h2,h3" port=443 mandatory=alpn,port` |
| **SVCB** (or **HTTPS**) | `_a2a._agents` | `1` | `chatmodel.in.` | `alpn="a2a" port=443 mandatory=alpn,port` |

**BIND / Zone File Format:**
```dns
_index._agents.chatmodel.in. 3600 IN SVCB 1 chatmodel.in. alpn="h2,h3" port=443 mandatory=alpn,port
_a2a._agents.chatmodel.in.   3600 IN SVCB 1 chatmodel.in. alpn="a2a" port=443 mandatory=alpn,port
```

2. **Enable DNSSEC**:
   - In Cloudflare DNS (or your registrar), go to **DNS** > **Settings** > **DNSSEC** and click **Enable DNSSEC**.
   - Copy the DS record to your domain registrar (GoDaddy, Namecheap, etc.) so validating DNS resolvers return authenticated data.

> **Cloudflare Tip**: If using Cloudflare proxy (orange cloud), ensure you have an SSL certificate that covers `*.chatmodel.in` (Cloudflare Universal SSL covers wildcard subdomains automatically). You can also enable **Markdown for Agents** in Cloudflare (AI & Speed settings) for edge-level markdown conversion.

---

## 💻 Step 2: Automated 1-Command Server Setup (Ubuntu / Debian)

Clone the repository to `/var/www/html/chatmodel` and run the included deployment script:

```bash
cd /var/www/html/chatmodel
sudo bash deploy/deploy.sh
```

---

## 🔧 Step 3: Manual Step-by-Step Setup (Nginx)

If you prefer manual setup instead of the script:

### 1. Install Dependencies
```bash
sudo apt update
sudo apt install -y nginx php8.2-fpm php8.2-sqlite3 php8.2-curl php8.2-mbstring php8.2-xml certbot python3-certbot-nginx
```

### 2. Copy Code & Set Directory Permissions
```bash
sudo mkdir -p /var/www/html/chatmodel
sudo cp -r . /var/www/html/chatmodel/
sudo chown -R www-data:www-data /var/www/html/chatmodel
sudo chmod -R 755 /var/www/html/chatmodel
sudo chmod -R 775 /var/www/html/chatmodel/data
```

### 3. Configure Nginx
```bash
sudo cp /var/www/html/chatmodel/deploy/nginx-wildcard.conf /etc/nginx/sites-available/chatmodel
sudo ln -sf /etc/nginx/sites-available/chatmodel /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart php8.2-fpm nginx
```

---

## 🔒 Step 4: Wildcard SSL Certificate (HTTPS)

To secure both the root domain `chatmodel.in` and all subdomains `*.chatmodel.in`:

```bash
sudo certbot certonly --manual --preferred-challenges dns -d "chatmodel.in" -d "*.chatmodel.in"
```

Follow the on-screen prompt to add the `_acme-challenge` TXT record in your DNS manager. Once verified, enable SSL in your Nginx configuration:

```nginx
listen 443 ssl http2;
ssl_certificate /etc/letsencrypt/live/chatmodel.in/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/chatmodel.in/privkey.pem;
```

And restart Nginx:
```bash
sudo systemctl restart nginx
```

---

## 🏢 Step 5: Custom Whitelabel CNAME Domains (e.g. `chat.client.com`)

ChatModel comes with built-in DNS CNAME inspection and automatic routing:
1. When a client wants to connect their domain (e.g. `chat.acmecorp.com`), they create a **CNAME** in their DNS pointing to `chatmodel.in` or `acmecorp.chatmodel.in`.
2. In the Client or Admin Portal, enter `chat.acmecorp.com` and click **⚡ Verify DNS**.
3. Nginx's catch-all `_` server block automatically routes incoming traffic from any custom domain to `index.php`, where ChatModel resolves the host header and renders their assistant branded workspace!

---

## 🛡️ Security Checklists Verified
- [x] SQLite database directory `/data/` and `/db/` are completely blocked from HTTP access (`404 / 403`).
- [x] SQLite enabled WAL mode (`PRAGMA journal_mode = WAL;`) for high concurrency.
- [x] Security headers set (`X-Content-Type-Options: nosniff`, `X-XSS-Protection: 1; mode=block`).
- [x] Iframe embedding permitted for client website assistant widgets.
- [x] CORS enabled for API endpoints.
- [x] Zero references to backend infrastructure or internal tools.
