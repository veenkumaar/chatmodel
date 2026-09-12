#!/bin/bash
# ==============================================================================
# ChatModel SaaS - Automated Production Server Setup Script
# Works on: Ubuntu 22.04 / 24.04 LTS, Debian 11 / 12
# ==============================================================================

set -e

echo "========================================================="
echo "🚀 Initializing ChatModel Production Deployment"
echo "========================================================="

# 1. Update Packages & Install Required PHP Extensions
echo "📦 Installing PHP, SQLite, cURL, Nginx, and Certbot..."
apt update && apt install -y nginx php-fpm php-sqlite3 php-curl php-mbstring php-xml certbot python3-certbot-nginx

# 2. Detect PHP version
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo " Detected PHP Version: ${PHP_VERSION}"

# 3. Create Web Directory if not existing
APP_DIR="/var/www/html/chatmodel"
if [ ! -d "$APP_DIR" ]; then
    echo "📁 Creating application directory at ${APP_DIR}..."
    mkdir -p "$APP_DIR"
fi

# 4. Set Permissions for SQLite Database & Web Server
echo "🔒 Configuring filesystem permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"

if [ -d "$APP_DIR/data" ]; then
    chmod -R 775 "$APP_DIR/data"
fi

# 5. Link Nginx Configuration
echo "⚙️ Configuring Nginx VirtualHost..."
sed -i "s/php8.2-fpm.sock/php${PHP_VERSION}-fpm.sock/g" "$APP_DIR/deploy/nginx-wildcard.conf" 2>/dev/null || true
cp "$APP_DIR/deploy/nginx-wildcard.conf" /etc/nginx/sites-available/chatmodel
ln -sf /etc/nginx/sites-available/chatmodel /etc/nginx/sites-enabled/chatmodel
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# 6. Test Nginx and Restart Services
echo "🧪 Testing Nginx configuration..."
nginx -t

echo "🔄 Restarting Nginx and PHP-FPM..."
systemctl restart "php${PHP_VERSION}-fpm"
systemctl restart nginx

echo "========================================================="
echo "✅ ChatModel is now live and running!"
echo ""
echo "Next step: Run Certbot to generate your Wildcard SSL Certificate:"
echo "certbot certonly --manual --preferred-challenges dns -d 'chatmodel.in' -d '*.chatmodel.in'"
echo "========================================================="
