# VoyageGo Production Deployment & Operations Runbook

## 1. Hosting Environment & Server Requirements

- **Operating System**: Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9) or Windows Server
- **PHP**: PHP 8.2 or later
  - Required Extensions: `pdo`, `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `json`, `session`, `gd`
  - In `php.ini`:
    ```ini
    session.use_strict_mode = 1
    session.cookie_httponly = 1
    session.cookie_secure = 1 ; When running HTTPS
    expose_php = Off
    display_errors = Off
    log_errors = On
    error_log = /path/to/storage/logs/php_errors.log
    upload_max_filesize = 5M
    post_max_size = 8M
    ```
- **Database Engine**: MySQL 8.0+ or MariaDB 10.5+ with InnoDB engine support and `utf8mb4_unicode_ci` collation.

---

## 2. Database User Segregation

Whenever hosting permits, separate the privileged installation/migration account from the everyday runtime account:

### A. Privileged Installer Account (Setup & Migrations Only)
```sql
CREATE USER 'voyagego_setup'@'localhost' IDENTIFIED BY 'STRONG_SETUP_PASSWORD';
GRANT CREATE, ALTER, INDEX, REFERENCES, SELECT, INSERT, UPDATE, DELETE, DROP ON tours_travels_db.* TO 'voyagego_setup'@'localhost';
FLUSH PRIVILEGES;
```

### B. Daily Runtime Account (Web Application Traffic)
```sql
CREATE USER 'voyagego_app'@'localhost' IDENTIFIED BY 'STRONG_RUNTIME_PASSWORD';
-- No DDL (DROP, ALTER, CREATE) permitted at runtime
GRANT SELECT, INSERT, UPDATE, DELETE ON tours_travels_db.* TO 'voyagego_app'@'localhost';
FLUSH PRIVILEGES;
```

Update [config/database.php](file:///e:/All-Projects/MY-PROJECTS/Tours_travels/config/database.php):
```php
'default' => [
    'username' => 'voyagego_app',
    'password' => 'STRONG_RUNTIME_PASSWORD',
],
'installer' => [
    'username' => 'voyagego_setup',
    'password' => 'STRONG_SETUP_PASSWORD',
],
```

---

## 3. Directory Structure & File Permissions

Ensure the web server document root points **strictly** to the `public/` directory, preventing direct web exposure of `app/`, `config/`, and `storage/`.

```bash
# Set ownership to web user (e.g. www-data on Ubuntu)
chown -R www-data:www-data /var/www/voyagego

# Restrict permissions
find /var/www/voyagego -type f -exec chmod 644 {} \;
find /var/www/voyagego -type d -exec chmod 755 {} \;

# Ensure write access for logs, backups, and private uploads
chmod -R 775 /var/www/voyagego/storage
chmod 775 /var/www/voyagego/config
```

---

## 4. Web Server Configuration

### Nginx Virtual Host Configuration
```nginx
server {
    listen 80;
    server_name travels.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name travels.yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/travels.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/travels.yourdomain.com/privkey.pem;

    root /var/www/voyagego/public;
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

### Apache `.htaccess` (Inside `public/`)
Create `public/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## 5. Automated Backups & Disaster Recovery

### Automatic Cron Job
Add to system crontab (`crontab -e`):
```cron
# Execute daily database backup snapshot at 02:00 AM
0 2 * * * php /var/www/voyagego/tests/run_all.php > /dev/null 2>&1
```

### Restoration Runbook
To restore from a backup snapshot:
1. Log in to the Admin Command Center: `/office/backups`
2. Review available snapshots in `storage/backups/`.
3. Click **Restore Snapshot** on the desired archive.
4. The system validates the SQL dump signature and imports schema and records inside a safe transaction block.

---

## 6. Troubleshooting Guide

| Symptom | Diagnostic Check | Resolution |
| :--- | :--- | :--- |
| **"Database connection failed"** | Check `storage/logs/error-*.log` | Confirm MySQL service is running and credentials in `config/database.php` are correct. |
| **"CSRF security token invalid"** | Inspect session cookie domain | Clear browser cookies. Verify domain in `app/Core/Session.php` has no port number. |
| **"Installation Locked" on `/setup`** | Check `config/installed.lock` | The installer is intentionally locked after setup. Remove `installed.lock` only during authorized staging resets. |
| **Chauffeur GPS Update Fails (422)** | Check driver trip status | Chauffeur must have an active trip (`assigned`, `accepted`, or `started`). Idle drivers cannot transmit telemetry. |
| **Rate Limit 429 Error** | Check `storage/rate_limits/` | Wait the displayed lockout duration (typically 60s or 15 mins for failed logins). Or delete `storage/rate_limits/rl_*.json` in emergency. |
