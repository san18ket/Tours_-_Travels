# VoyageGo — Tours & Travels Fleet Operations System

[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Custom MVC Architecture](https://img.shields.io/badge/Architecture-Custom%20MVC%20%2B%20Services-0B192C.svg)](#architecture)
[![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20MariaDB%20%28PDO%29-008DDA.svg)](#automatic-database-initialization)
[![Tests Passed](https://img.shields.io/badge/Tests-41%2F41%20Passing-10B981.svg)](#testing-gates)
[![PWA Ready](https://img.shields.io/badge/PWA-Installable%20%26%20Offline%20Cache-1E3E62.svg)](#pwa--driver-mobile-console)

VoyageGo is an enterprise-grade Tours & Travels Fleet Dispatch, Tour Package Booking, and Chauffeur Operations Management System built with **Core PHP**, a **Custom MVC Architecture**, **MySQL (PDO)**, **HTML5**, **Vanilla CSS**, and **Vanilla JavaScript** (strictly zero external heavy frontend frameworks).

---

## 🌟 Key Functional Highlights

1. **Office Command Operations Dashboard**:
   - Real-time KPI metrics: Active trips with live pulse radar, total bookings, available fleet vehicles, drivers on duty, and invoiced revenue.
   - Live dispatch radar table and recent reservation feeds.
   - Immutable security and operational audit stream.

2. **Tour Packages Management**:
   - Dynamic catalog supporting Heritage, Hill Station, Pilgrimage, Wildlife Safari, Beach, and Custom Corporate travel itineraries.
   - Fixed fare structures, inclusion/exclusion specifications, and passenger capacity rules.

3. **Fleet & Chauffeur Roster**:
   - Vehicle registration: commercial plate, make & model, capacity (Sedans, SUVs, Tempo Travellers, Luxury Coaches), odometer log, and roadworthiness status.
   - Chauffeur profiles: commercial license verification, experience years, driver ratings, and duty toggling (`on_duty` / `off_duty`).

4. **Dispatch Operations & Lifecycle Engine**:
   - Multi-stage state machine: `assigned` → `driver_accepted` → `en_route_pickup` → `trip_started` → `completed`.
   - Start and ending odometer verification (enforcing strictly non-decreasing mileage).

5. **Tax Invoicing & Financial Ledger**:
   - Itemized tax breakdown: Tour subtotal, promotional discounts, and Goods & Services Tax (GST 5%).
   - **Issued-Invoice Integrity**: Once generated, tax invoices are marked `is_immutable = 1`. Core financials cannot be altered or deleted.
   - **Idempotent Payment Allocation**: Prevents double-charging or duplicate submission via unique transaction tokens (`idempotency_key`).
   - Printable official tax invoice with company GSTIN and payment allocation details.

6. **Driver Mobile Portal (PWA)**:
   - Mobile-first chauffeur duty console with adaptive bottom navigation.
   - Touch-friendly action triggers (minimum 44x44px touch targets).
   - Live GPS telemetry reporting linked to authorized active trips.
   - Offline fallback banner and background asset cache.

---

## 🔒 Security Architecture

- **Authentication & Sessions**:
  - Passwords hashed using `PASSWORD_BCRYPT` with cost factor 12.
  - Session fixation protection: `session_regenerate_id(true)` executed upon login.
  - Cookie flags: `HttpOnly`, `SameSite=Lax`, strict mode enabled, and 30-minute idle session timeouts.
- **CSRF Protection**:
  - Cryptographically secure 64-character tokens (`random_bytes(32)`) required on every state-changing request (POST/PUT/DELETE). Timing-safe verification using `hash_equals`.
- **SQL Injection Immunity**:
  - 100% parameter-bound PDO prepared statements across all queries. Zero dynamic SQL string concatenation.
- **XSS Defense**:
  - Contextual output escaping with `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`) in all views.
- **Authorization & Ownership (IDOR Prevention)**:
  - Role-based middleware (`admin`, `dispatcher`, `driver`, `customer`).
  - Strict record-level ownership checks preventing guests or drivers from viewing or modifying records outside their authorized scope.
- **Rate Limiting & Throttle**:
  - Login lockout defense (maximum 5 failed attempts per 15 minutes per IP & user).
  - API rate limits (120 req/min) and GPS submission rate limits.
- **Service Worker Data Protection**:
  - Service worker cache strategy strictly excludes private routes (`/office/invoices`, `/office/bookings`, `/private_uploads`, `/api/`).
- **Telemetry Authorization**:
  - Driver GPS coordinates are validated against active, authorized trip dispatches. Submissions from drivers without active duty or impossible coordinates are firmly rejected.

---

## 🗄️ Automatic Database Initialization & Migrations

VoyageGo features a zero-touch, resilient database migration system:
1. **Connectivity Check**: Bootstrap verifies DB accessibility and schema version on application entry.
2. **Advisory Locking**: Prevents race conditions during concurrent deployments using MySQL `GET_LOCK('voyagego_migration_lock', 20)`.
3. **Versioned & Idempotent Migrations**: Located in `app/Migrations/`. Automatically creates missing tables, indexes, and foreign keys.
4. **Migration History**: Maintained in `schema_migrations` tracking filename, batch, status, and duration in milliseconds.
5. **No Data Loss Guarantee**: Migration files never drop tables, truncate records, or alter business data.
6. **Installer Lockout**: Upon initial setup, `config/installed.lock` is written. Future access to `/setup` is blocked to prevent unauthorized re-installation.
7. **Privileged vs. Runtime Accounts**: Supports distinct credentials in `config/database.php` for setup operations vs. daily runtime execution.

---

## 🚀 Quick Start Guide

### Prerequisites
- PHP 8.2 or later with extensions: `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `json`, `session`.
- MySQL 8.0+ or MariaDB 10.4+.
- Web server: Apache, Nginx, or PHP CLI built-in server.

### Setup Steps

1. **Configure Database Credentials**:
   Edit [config/database.php](file:///e:/All-Projects/MY-PROJECTS/Tours_travels/config/database.php):
   ```php
   'default' => [
       'host'     => '127.0.0.1',
       'port'     => 3306,
       'database' => 'tours_travels_db',
       'username' => 'root',
       'password' => '',
   ]
   ```

2. **Initialize Database & Seed Data**:
   Navigate to `http://127.0.0.1:8080/setup` in your browser, or run via CLI:
   ```powershell
   php tests/run_all.php
   ```

3. **Start the Development Server**:
   ```powershell
   php -S 127.0.0.1:8080 -t public
   ```

4. **Access the Application**:
   Open `http://127.0.0.1:8080/login`

### Default Demo Accounts

| Role | Email | Password | Primary Portal |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@voyagego.com` | `Admin@12345` | `/office/dashboard` |
| **Dispatcher** | `dispatcher@voyagego.com` | `Dispatcher@12345` | `/office/dispatch` |
| **Chauffeur / Driver** | `rajesh.driver@voyagego.com` | `Driver@12345` | `/driver/duty` |
| **Customer / Guest** | `arun.sharma@example.com` | `Customer@12345` | `/office/bookings` |

*(Quick-click selector buttons are also available directly on the login form for rapid evaluation).*

---

## 🧪 Testing Gates & Verification

Run the automated verification suite:
```powershell
php tests/run_all.php
```

### Verified Test Suites:
- `Database Initialization & Versioned Migrations`: Connectivity, idempotent DDL, migration history, concurrent `GET_LOCK` locking, and installation lock integrity.
- `Security, Authentication, XSS & SQLi Defense`: Bcrypt password verification, session fixation prevention, 64-char CSRF timing-safe comparison, output escaping, parameterized query SQLi immunity, rate limiter lockout.
- `Business Rules, Pricing, Invoices & Idempotency`: Pricing formula (5% GST), duplicate booking prevention via idempotency token, invoice immutability enforcement, duplicate payment prevention, GPS coordinate validation.
- `Database Backup, Integrity Checksum & Retention`: SQL dump export, SHA-256 checksum calculation, transactional restoration procedure, and 10-snapshot retention policy.
