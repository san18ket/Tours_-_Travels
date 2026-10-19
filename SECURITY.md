# VoyageGo Security Policy & Threat Verification Matrix

## Security Model Overview

VoyageGo implements a defense-in-depth posture adhering to the OWASP Top 10 guidelines and strict financial compliance standards.

---

## 1. Threat Mitigation & Verification Matrix

| Vulnerability Category | Mitigation Architecture | Verification Mechanism |
| :--- | :--- | :--- |
| **SQL Injection (SQLi)** | Strict parameter binding via PDO prepared statements. Dynamic table/column names are strictly sanitized against alphanumeric whitelists. Zero concatenated SQL strings. | Automated test suite executes attack payload `' OR '1'='1` and verifies exact parameterization. |
| **Cross-Site Scripting (XSS)** | Contextual escaping function `e()` wrapping `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Content-Security-Policy headers enforced. | Test suite asserts that `<script>alert(1)</script>` is encoded to `&lt;script&gt;` with quotes converted. |
| **Cross-Site Request Forgery (CSRF)** | Synchronizer token pattern with cryptographically secure 64-character tokens. Verified using timing-safe string comparison (`hash_equals`). | Tests verify rejection of forged, tampered, or missing tokens on state-changing routes. |
| **Session Fixation & Hijacking** | Immediate session ID regeneration on successful authentication (`session_regenerate_id(true)`). Cookie flags: `HttpOnly`, `SameSite=Lax`, strict mode, and 30-min idle timeout. | Automated session regeneration tests and cookie attribute validation. |
| **Insecure Direct Object References (IDOR)** | Dual-tier authorization: route-level role middleware combined with service-level record ownership checks (`authorizeOwnership()`). | Unit tests verify customers cannot inspect foreign booking references or private invoices. |
| **Financial Mutation & Invoicing Tampering** | Finalized tax invoices have `is_immutable = 1`. Any programmatic or SQL attempt to alter subtotal, tax, or delete an issued invoice throws `RuntimeException`. | Automated test attempts to modify subtotal and delete invoice; asserts exception is thrown. |
| **Double Charging & Duplicate Requests** | Idempotency token enforcement across bookings and payments (`idempotency_token`, `idempotency_key`) within database transaction boundaries. | Test suite fires duplicate payment submissions with matching keys; confirms duplicate is blocked and existing record returned. |
| **Rogue GPS Submissions** | Drivers can only transmit coordinates if they possess an active, authorized trip assignment (`dispatches.status IN ('driver_accepted', 'en_route_pickup', 'trip_started')`). | Test suite asserts rejection of GPS telemetry for idle drivers or impossible coordinates (>90° lat). |
| **Brute Force & DoS** | Centralized rate limiting with exponential backoff on login endpoints (5 attempts / 15 mins) and telemetry submissions. | Test suite exercises threshold boundary and confirms 429 lockout. |
| **Data Loss During Migrations** | Versioned, idempotent migrations with MySQL distributed advisory locking (`GET_LOCK`). Migrations never drop tables or truncate records. | Test suite runs migration suite repeatedly and asserts 0 data loss and 0 pending schema changes. |

---

## 2. Reporting a Vulnerability

If you discover a security vulnerability in VoyageGo, please email `security@voyagego.internal`. All security advisories are reviewed promptly by the security engineering team.
