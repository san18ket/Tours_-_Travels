# Full Localization System Architecture & Developer Guide

## Tours & Travels Management System
**Supported Locales:** English (`en`), Hindi (`hi`), Marathi (`mr`)  
**Single Source of Truth:** `App\Core\Language` & `admin/app/Languages/`

---

## 1. Architecture Overview

The Tours & Travels localization system is designed around a single, highly performant, server-side source of truth with zero client-side translation lag and zero English render flashing.

```
                  ┌────────────────────────────────────────┐
                  │          Incoming HTTP Request         │
                  └───────────────────┬────────────────────┘
                                      │
                                      ▼
                  ┌────────────────────────────────────────┐
                  │      Bootstrap Resolution Priority     │
                  │   1. Explicit Query (?lang=hi)         │
                  │   2. User Session ($_SESSION['locale']) │
                  │   3. Persistent Cookie ('tt_lang')     │
                  │   4. User Profile DB Preference        │
                  │   5. Fallback Default ('en')           │
                  └───────────────────┬────────────────────┘
                                      │
                                      ▼
                  ┌────────────────────────────────────────┐
                  │        App\Core\Language::init()       │
                  │ - Loads target dictionary (en/hi/mr)   │
                  │ - Sets HTML lang and dir attributes   │
                  │ - Seeds JavaScript window.__() payload │
                  └───────────────────┬────────────────────┘
                                      │
                    ┌─────────────────┴─────────────────┐
                    ▼                                   ▼
        ┌───────────────────────┐           ┌───────────────────────┐
        │  PHP Views & Backend  │           │  Frontend JavaScript  │
        │      __('key')        │           │    window.__('key')   │
        └───────────────────────┘           └───────────────────────┘
```

### Core Components
1. **`App\Core\Language` (`admin/app/Core/Language.php`)**:
   Manages locale state, supported locales, dictionary array caching, placeholder replacements, and script directions (`ltr`).
2. **`LanguageController` (`admin/app/Controllers/LanguageController.php`)**:
   Handles language change actions (`/language/change/{locale}` and `?lang={locale}`). Updates session and 365-day secure cookies (`SameSite=Lax`), updates user database preferences when authenticated, and safely redirects the user back to the exact current page via sanitized `HTTP_REFERER` without logging out or dropping request context.
3. **`__()` Global Translation Helper (`admin/app/Helpers/functions.php`)**:
   Resolves keys with dot notation (e.g. `booking.customer`), interpolates `:param` placeholders, and transparently falls back to English if a localized string is missing.

---

## 2. File & Directory Locations

| File / Directory | Purpose |
| :--- | :--- |
| `admin/app/Core/Language.php` | Core localization engine, state manager, and dictionary loader |
| `admin/app/Controllers/LanguageController.php` | Controller for locale switching and persistence |
| `admin/app/Languages/en.php` | English translation dictionary (Base / Master) |
| `admin/app/Languages/hi.php` | Hindi translation dictionary |
| `admin/app/Languages/mr.php` | Marathi translation dictionary |
| `admin/app/Helpers/functions.php` | Global helpers: `__()`, `format_currency()`, `format_date()`, `render_status_badge()` |
| `admin/bin/audit_localization.php` | CLI tool for dictionary validation and parity audits |
| `admin/tests/test_localization_audit.php` | Automated test suite verifying localization behavior |
| `admin/docs/LOCALIZATION.md` | This technical guide |

---

## 3. How to Add New Translation Keys

All keys must maintain **100% parity across `en.php`, `hi.php`, and `mr.php`**. Never add a key to one dictionary without adding it to the other two.

### Step-by-Step Procedure:
1. **Choose a Structured Key Name:**
   Follow standard hierarchical dot notation:
   - Domain actions/entities: `booking.travel_dates`, `customer.mobile`, `vehicle.odometer`
   - Common buttons: `btn.save`, `btn.cancel`, `btn.edit`, `btn.delete`, `btn.search`
   - Common table headers: `table.action`, `table.status`, `table.created_at`
   - Input placeholders: `placeholder.search_bookings`, `placeholder.enter_mobile`
   - Toast/Alert messages: `flash.saved_successfully`, `alert.delete_confirm`

2. **Add to `admin/app/Languages/en.php`:**
   ```php
   'booking.reschedule_trip' => 'Reschedule Trip',
   ```

3. **Add to `admin/app/Languages/hi.php`:**
   ```php
   'booking.reschedule_trip' => 'यात्रा पुनर्निर्धारित करें',
   ```

4. **Add to `admin/app/Languages/mr.php`:**
   ```php
   'booking.reschedule_trip' => 'प्रवासाची तारीख बदला',
   ```

5. **Run the Audit Command:**
   ```bash
   php admin/bin/audit_localization.php
   ```
   Confirm that key counts remain identical and zero errors are reported.

### Dynamic Placeholders:
Use `:variable` syntax in keys requiring runtime values. Placeholders must be preserved identically across all three dictionaries:
- `en.php`: `'table.pagination_info' => 'Showing :from–:to of :total entries',`
- `hi.php`: `'table.pagination_info' => 'कुल :total में से :from–:to प्रविष्टियां',`
- `mr.php`: `'table.pagination_info' => 'एकूण :total पैकी :from–:to नोंदी',`

In PHP templates or controllers:
```php
echo __('table.pagination_info', ['from' => 1, 'to' => 25, 'total' => 150]);
```

---

## 4. How to Add a New Language (e.g. Gujarati `gu`)

1. **Register the Locale in `App\Core\Language`:**
   Open `admin/app/Core/Language.php` and add the language to the `SUPPORTED` constant:
   ```php
   public const SUPPORTED = [
       'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
       'hi' => ['name' => 'Hindi',   'native' => 'हिन्दी',   'dir' => 'ltr'],
       'mr' => ['name' => 'Marathi', 'native' => 'मराठी',   'dir' => 'ltr'],
       'gu' => ['name' => 'Gujarati','native' => 'ગુજરાતી', 'dir' => 'ltr'],
   ];
   ```

2. **Create the Dictionary File:**
   Copy `admin/app/Languages/en.php` to `admin/app/Languages/gu.php`.
   Translate all key values to natural, professional Gujarati.

3. **Verify Integrity via CLI:**
   ```bash
   php admin/bin/audit_localization.php
   ```

4. **Update the UI Dropdown Selectors:**
   The top navbar language dropdowns in `admin/app/Views/layouts/office.php` and `driver.php` automatically iterate through `Language::SUPPORTED` and render the new language option dynamically.

---

## 5. JavaScript Localization

Frontend JavaScript components (dynamic modals, AJAX tables, SweetAlert / Toast confirmations) access localized strings via `window.__()`:

### How it Works:
During layout initialization (`office.php` / `driver.php`), key UI strings are serialized into a lightweight window object:
```html
<script>
    window.__translations = <?= json_encode(\App\Core\Language::getJsTranslations(), JSON_UNESCAPED_UNICODE) ?>;
    window.__locale = "<?= \App\Core\Language::getLocale() ?>";
    window.__ = function(key, params) {
        var str = (window.__translations && window.__translations[key]) || key;
        if (params) {
            for (var p in params) {
                str = str.replace(new RegExp(':' + p, 'g'), params[p]);
            }
        }
        return str;
    };
</script>
```

### In JavaScript Code:
```javascript
// Confirmation dialog
if (confirm(window.__('booking.delete_confirmation'))) {
    // Proceed with deletion
}

// Dynamic notification toast
showToast(window.__('flash.saved_successfully'));
```

---

## 6. Formatting Helpers

### Currency:
Always format currency using `format_currency()` or `__currency()`:
```php
echo format_currency(12500.50);
// Outputs: ₹ 12,500.50 in Indian Numbering System (Lakhs / Crores)
```

### Dates and Times:
Use standard date helpers:
```php
echo format_date($booking['pickup_date']); 
// Outputs: 15 Oct 2026
```

### Status Badges:
Use `render_status_badge($status)` to render a styled, localized badge while keeping the database value untouched:
```php
echo render_status_badge('confirmed');
// Outputs: <span class="badge badge-success">पुष्टि की गई</span> (in Hindi)
```

---

## 7. Critical Rules & Common Pitfalls

### Rule 1: NEVER Translate Dynamic Business Data
Localization applies exclusively to the user interface (labels, navigation, placeholders, error alerts). Dynamic data fetched from the database must remain unaltered:
- **Customer / Employee / Driver Names:** "Rahul Patil", "Vikram Singh"
- **Vehicle Numbers & Plates:** "MH15AB1234", "DL01XY9876"
- **Booking References:** "BK-1004", "TRIP-2026-09"
- **Physical Addresses & User Notes:** "Flat 402, Shanti Nagar, Nashik"

### Rule 2: NEVER Alter Database Enum / Status Values
Internal database status values must remain lowercase ASCII tokens:
```
Database values: 'pending', 'confirmed', 'assigned', 'in_progress', 'completed', 'cancelled'
```
Never store or query localized strings like `लंबित` or `रद्द` in database tables. Always use:
```php
// Good:
$badgeText = __('status.' . $row['status']);

// Bad:
$row['status'] = __('status.pending'); // NEVER DO THIS
```

### Rule 3: Avoid String Concatenation / Fragmentation
Never construct sentences by concatenating translated words:
```php
// BAD: Fragmented translation
echo __('common.showing') . ' ' . $count . ' ' . __('common.results');

// GOOD: Complete templated phrase
echo __('table.showing_results', ['count' => $count]);
```

### Rule 4: Mask Technical Exceptions Before Display
Never expose raw PDO exceptions, table names, or SQLSTATE strings to end users.
Always wrap DB operations in `try/catch` and pass exceptions through `ErrorMessageTranslator::translate($e, 'context', 'action')`.

---

## 8. CLI Audit & Verification Commands

Run the localization audit script at any time to verify system health:
```bash
php admin/bin/audit_localization.php
```

Run the automated test suite:
```bash
php admin/tests/test_localization_audit.php
```
Both commands return exit code `0` on success and non-zero on error, making them ideal for CI/CD pipelines and deployment hooks.
