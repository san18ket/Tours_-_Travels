# Tours & Travels Management System — Production Operations & Deployment Guide

## 1. System Requirements & Architecture

- **Operating System**: Windows (XAMPP / IIS / Windows Server) or Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9)
- **PHP Version**: PHP 8.2 or later (8.2.12+ recommended)
  - Required Extensions: `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `json`, `session`, `gd`, `curl`
  - Recommended `php.ini` Directives:
    ```ini
    session.use_strict_mode = 1
    session.cookie_httponly = 1
    session.cookie_samesite = "Lax"
    session.cookie_secure = 0 ; Set to 1 when serving over HTTPS
    expose_php = Off
    display_errors = Off
    log_errors = On
    error_log = storage/logs/php_errors.log
    upload_max_filesize = 10M
    post_max_size = 12M
    max_execution_time = 60
    ```
- **Database**: MySQL 8.0+ or MariaDB 10.4+ with InnoDB engine support and `utf8mb4_unicode_ci` collation.
- **Frontend / Browser Requirements**: Modern Evergreen browser (Chrome, Edge, Firefox, Safari) with Service Worker and IndexedDB support.

---

## 2. Quick-Start Deployment (Windows / XAMPP)

### Step 1: Extract Release Archive
Extract `tours_travels_release.zip` into your web server directory:
```
E:\xampp\htdocs\tours-travels\   (for subdirectory deployment)
OR
E:\All-Projects\MY-PROJECTS\Tours_travels\  (for root / virtual host)
```

### Step 2: Configure Environment
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Edit `.env` to configure your database and SMTP credentials:
```ini
APP_NAME="Tours & Travels Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/tours-travels

# Database Configuration
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tours_travels_db
DB_USERNAME=root
DB_PASSWORD=

# SMTP Mail Configuration (Gmail SSL Port 465 or custom SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Tours & Travels"
MAIL_ADMIN_ADDRESS=admin@example.com
```

### Step 3: Initialize Database & Run Migrations
1. Ensure MySQL is running in XAMPP.
2. Create the application database:
   ```sql
   CREATE DATABASE IF NOT EXISTS `tours_travels_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Run the migrations via CLI:
   ```powershell
   & "E:\xampp\php\php.exe" bin/migrate.php
   ```
   *(Or visit `http://localhost/tours-travels/setup` in your browser to complete guided setup)*.

---

## 3. Background Queue Worker Setup (Windows & Linux)

The system supports reliable asynchronous email delivery and notification queues.

### Windows Background Worker (XAMPP / Task Scheduler)
Run the queue worker via PowerShell or Command Prompt:
```powershell
& "E:\xampp\php\php.exe" bin/queue_worker.php
```
To run as a persistent Windows service or scheduled task:
1. Open **Windows Task Scheduler** (`taskschd.msc`).
2. Create a Basic Task named `ToursTravelsQueueWorker`.
3. Set Trigger to **At startup** or **Every 5 minutes**.
4. Action: Start a Program:
   - Program: `E:\xampp\php\php.exe`
   - Arguments: `E:\All-Projects\MY-PROJECTS\Tours_travels\bin\queue_worker.php`

### Linux Systemd Service (`/etc/systemd/system/tours-queue.service`)
```ini
[Unit]
Description=Tours & Travels Background Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/tours-travels
ExecStart=/usr/bin/php /var/www/tours-travels/bin/queue_worker.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

---

## 4. Directory Structure & Permission Requirements

Ensure write permissions for the web server on the storage directory:
```bash
# Storage subdirectories must be writable
chmod -R 775 storage/
chmod -R 775 storage/logs/
chmod -R 775 storage/cache/
chmod -R 775 storage/sessions/
chmod -R 775 storage/uploads/
chmod -R 775 storage/private_uploads/
chmod -R 775 storage/rate_limits/
```

On Windows / XAMPP, ensure NTFS write permissions for `Users` or `IIS_IUSRS` on the `storage\` directory.

---

## 5. Web Server Virtual Host Configurations

### Apache (XAMPP / Linux with `mod_rewrite`)
Ensure `public/.htaccess` is active:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name travels.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name travels.example.com;

    ssl_certificate /etc/letsencrypt/live/travels.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/travels.example.com/privkey.pem;

    root /var/www/tours-travels/public;
    index index.php;

    # Security Headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Block access to hidden files and storage
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 6. Progressive Web App (PWA) & Browser Caching

1. **Service Worker (`public/sw.js`)**:
   - Manages versioned static caching (`tours-static-v6.0`, `tours-shell-v6.0`).
   - Automatically supports root (`/`) and subdirectory (`/tours-travels/`) deployments.
   - **Private Route Protection**: Sensitive routes (`/office`, `/driver`, `/login`, `/api/mobile`, `/finance`) are strictly bypassed by the Service Worker and never stored in browser caches.
2. **IndexedDB Reference Cache (`tours-cache.js`)**:
   - Centralized local client cache for static reference data (locations, vehicle categories, payment methods).
   - In-flight request deduplication prevents redundant simultaneous fetches.
   - Transparent in-memory fallback if IndexedDB is disabled in private browsing modes.
   - Automatic cache version invalidation via ETag revalidation (HTTP 304 Not Modified).

---

## 7. Security Hardening Checklist

- [x] `.env` is located outside the public web root (`public/`).
- [x] Direct file access to `.env`, `.git/`, `storage/logs/`, `tests/` blocked at web server layer.
- [x] Remember Me split tokens stored as cryptographic SHA-256 hashes with atomic conditional rotation.
- [x] Session locks released early on read-only endpoints after all mutations are flushed.
- [x] File uploads verified with `finfo(FILEINFO_MIME_TYPE)`, restricted to whitelist, and stored with random names.
- [x] Rate limiting active on login, account recovery, OTP requests, and GPS telemetry endpoints.
- [x] Passwords, OTPs, and secrets completely masked from diagnostic logs.
- [x] Test runner guarded by strict database isolation (`TestEnvironment.php`), preventing production DB destruction.

---

## 8. Backup & Disaster Recovery

### Creating a Snapshot
1. Open the Admin Command Center: `/office/backups`
2. Click **Create Database Backup**.
3. The system generates an AES-verified SQL snapshot in `storage/backups/`.

### Restoring a Snapshot
1. Navigate to `/office/backups`.
2. Locate the desired snapshot and click **Restore Snapshot**.
3. The restore operation executes within an atomic transaction.

---

## 9. Release Manifest

- **Application Name**: Tours & Travels Management System
- **Version**: 2.5.0 Production Stable (Build 2026.11)
- **Release Package**: `tours_travels_release.zip`
- **Release Verification**: 100% Passed (18/18 Unit/Security Tests, 15/15 Concurrency Tests, 11/11 IndexedDB Tests, 300/300 HTTP Load Tests).
- **Driver Mobile App**: Compatible with React Native Chauffeur Console (`mobile-driver/`).
