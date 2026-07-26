# Deployment Guide
## Aprilo Commerce Assistant — Version 1.0

---

## 1. Document Control
* **Document Name:** Aprilo Commerce Assistant Deployment Guide
* **Version:** 1.0
* **Date:** July 26, 2026
* **Status:** Draft / Initial Release
* **Author:** Antigravity AI (on behalf of the Google DeepMind Advanced Agentic Coding Team)

---

## 2. Infrastructure Pre-requisites
* **Magento Server:** Magento Open Source or Adobe Commerce version 2.4.x, PHP 8.1 / 8.2.
* **Aprilo Platform Server:** Node.js v18+ or Laravel 10+, PostgreSQL 14+ with `pgvector` enabled, Redis 7+ for queue management, Nginx.

---

## 3. Magento Plugin Installation
Follow these commands to deploy the plugin to the Magento environment:

### Step 1: Copy Module Files
Upload the extension folder to your Magento installation directory:
```bash
mkdir -p app/code/Aprilo/CommerceAssistant
# Copy codebase into the folder
```

### Step 2: Register Module & Run Compilations
Run the standard Magento deployment sequence:
```powershell
bin/magento module:enable Aprilo_CommerceAssistant
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Step 3: Configure Crontab
Verify that the Magento system cron is configured. Edit the server crontab:
```bash
crontab -e
```
Add the following line (if not already present):
```text
* * * * * /usr/bin/php /var/www/html/magento2/bin/magento cron:run 2>&1 | grep -v "already running" >> /var/www/html/magento2/var/log/cron.log
```

---

## 4. Aprilo Platform Production Setup
Ensure database and queue processes are running.

### 4.1. Environment Variables Configuration (`.env`)
```ini
NODE_ENV=production
PORT=8080

# Database
DATABASE_URL="postgresql://postgres_user:strong_password@db-host:5432/aprilo_prod?schema=public"

# Redis Queue
REDIS_URL="redis://:redis_password@redis-host:6379/0"

# AI Models API
GEMINI_API_KEY="AIzaSyYourGeminiApiKeyHere"

# Cryptographic Keys
SHARED_API_SECRET="your_32_character_hex_signing_secret"
JWT_SECRET="another_long_jwt_signing_key_here"

# Storefront CORS
ALLOWED_ORIGINS="https://www.yourmagentostore.com"
```

### 4.2. Run Migrations & Start Server
Deploy database schema and spin up the cluster processes:
```bash
# Run database schema migrations
npm run db:migrate:deploy

# Start production server clusters using PM2 or systemd
pm2 start dist/server.js --name "aprilo-gateway"
```

---

## 5. Nginx Reverse Proxy Template
Configure Nginx as a reverse proxy for the platform APIs and WebSockets.

```nginx
server {
    listen 443 ssl http2;
    server_name api.aprilo.com;

    ssl_certificate /etc/letsencrypt/live/api.aprilo.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.aprilo.com/privkey.pem;

    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```
