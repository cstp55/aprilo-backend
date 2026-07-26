# Server and Nginx Setup

## Recommendation

For local Sprint 1 development, Nginx is not required.

For production, use:

- Nginx as the public web server and reverse proxy.
- PHP-FPM for the Laravel API.
- Supervisor for Laravel queue workers.
- Cron for Laravel scheduler.
- PostgreSQL with pgvector.
- Redis for queues, cache, and rate limiting.
- S3-compatible storage for uploaded knowledge files.
- HTTPS through Let's Encrypt or a managed load balancer.

## Local Development

Backend:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\backend
php artisan serve --host=127.0.0.1 --port=8000
```

Frontend:

```powershell
cd C:\Users\chand\Documents\Codex\2026-07-06\n\product\frontend
C:\nodejs\npm.cmd run dev -- --hostname 127.0.0.1 --port 3000
```

Local URLs:

| Service | URL |
| --- | --- |
| Frontend | http://127.0.0.1:3000 |
| API | http://127.0.0.1:8000/api |
| Health | http://127.0.0.1:8000/up |

## Production Domains

Recommended domain split:

| Domain | Service |
| --- | --- |
| app.yourdomain.com | Next.js frontend |
| api.yourdomain.com | Laravel API |

This keeps frontend delivery and backend API scaling cleanly separated.

## Production Server Minimum

For an early pilot:

| Component | Minimum |
| --- | --- |
| App server | 2 vCPU / 4 GB RAM |
| Database | Managed PostgreSQL, 2 vCPU / 4 GB RAM |
| Redis | Managed Redis or same-server Redis for pilot only |
| Storage | S3-compatible bucket |

For real customer pilots, prefer a managed database and managed object storage.

## Laravel Nginx Example

Use this for `api.yourdomain.com` when Laravel is deployed to `/var/www/product/backend`.

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/product/backend/public;

    index index.php;

    client_max_body_size 25m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Next.js Nginx Reverse Proxy Example

Use this if self-hosting the frontend. Managed hosting such as Vercel is simpler for the frontend during early product validation.

```nginx
server {
    listen 80;
    server_name app.yourdomain.com;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

## Laravel Worker Setup

Queue worker should be managed by Supervisor.

```ini
[program:product-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/product/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/product/backend/storage/logs/worker.log
stopwaitsecs=3600
```

## Laravel Scheduler

Add this cron entry on the backend server:

```cron
* * * * * cd /var/www/product/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Environment Variables To Configure

Backend production `.env` should include:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=your-redis-host

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...

FRONTEND_URL=https://app.yourdomain.com
SANCTUM_STATEFUL_DOMAINS=app.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

## Deployment Checklist

- Install PHP, Composer, required PHP extensions, Nginx, and Supervisor.
- Deploy Laravel backend.
- Run `composer install --no-dev --optimize-autoloader`.
- Configure `.env`.
- Run `php artisan key:generate` if no app key exists.
- Run `php artisan migrate --force`.
- Run `php artisan storage:link` if public storage is needed.
- Run `php artisan config:cache`.
- Run `php artisan route:cache`.
- Start queue workers through Supervisor.
- Build and deploy Next.js frontend.
- Configure HTTPS.
- Validate `/up`, `/api/auth/login`, and frontend login.

## Current Sprint 1 Server Status

The code is ready to run locally. Codex verified the backend and frontend through automated checks, but the current sandbox refused detached background server launches, so no persistent local server process was left running from this chat.
