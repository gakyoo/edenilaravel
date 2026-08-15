# Edenire.co.tz — Deployment Guide

_How to take the platform from local dev to a live production server._

## Stack
- PHP 8.3+ (composer, ext: pdo_mysql, mbstring, xml, curl, gd, zip)
- MySQL 8 (db: `edenire_co_tz`)
- Node 20+ (for building assets)
- Nginx + PHP-FPM, or Laravel Octane/Supervisor
- Mailgun (transactional email: verification, tour notifications)

## 1. Server prerequisites
```bash
apt update && apt install -y nginx mysql-server php8.3-fpm php8.3-mysql \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-gd php8.3-zip composer
```

## 2. Get the code
```bash
git clone https://github.com/gakyoo/edenilaravel.git /var/www/edenire
cd /var/www/edenire
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

## 3. Environment
```bash
cp .env.example .env
php artisan key:generate
```
Set in `.env`:
- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://edenire.co.tz` (or your domain)
- `DB_DATABASE=edenire_co_tz`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_MAILER=mailgun`, `MAILGUN_DOMAIN=<your domain>`, `MAILGUN_SECRET=key-...`
- `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=.edenire.co.tz` (with HTTPS)
- `SANCTUM_STATEFUL_DOMAINS=edenire.co.tz` (for social login callbacks)

## 4. Database
```bash
mysql -e "CREATE DATABASE edenire_co_tz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
php artisan db:seed --force            # optional sample data
php artisan storage:link
```

## 5. Caches (after every deploy)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6. Nginx
```nginx
server {
    listen 80;
    server_name edenire.co.tz www.edenire.co.tz;
    root /var/www/edenire/public;
    index index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```
Then `certbot --nginx -d edenire.co.tz` for HTTPS.

## 7. Queue worker (if using queues for mail)
```ini
# /etc/systemd/system/edenire-queue.service
[Service]
User=www-data
WorkingDirectory=/var/www/edenire
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
Restart=always
```
```bash
systemctl enable --now edenire-queue
```

## 8. Mailgun notes (sandbox vs production)
- **Sandbox domain** (`sandboxXXXX.mailgun.org`): only sends to authorized recipients added in the dashboard. Use for testing only.
- **Production**: add your real domain in Mailgun → Sending → Domains → verify DNS (3 TXT + 1 MX/CNAME records), then set `MAILGUN_DOMAIN` + `MAILGUN_SECRET`.
- Sandbox/test recipients must be added under Sending → Domains → sandbox → Authorized recipients.

## 9. Post-deploy checklist
- [ ] `php artisan migrate --force` ran
- [ ] Storage link exists (`public/storage`)
- [ ] HTTPS works, mixed-content free (TrustProxies already configured)
- [ ] Admin user created: `php artisan tinker` → `User::create([... role=>'admin', email_verified_at=>now()])`
- [ ] Social login (Google/Facebook) credentials set in `.env`
- [ ] Test mail sends to g_akyoo@yahoo.com (authorized sandbox recipient)
- [ ] Backup plan: `mysqldump edenire_co_tz | gzip > backup-$(date +%F).sql.gz` + cron

## 10. Rollback
```bash
git log --oneline -5          # find previous good commit
git reset --hard <commit>
php artisan migrate:rollback  # if schema changed
```
