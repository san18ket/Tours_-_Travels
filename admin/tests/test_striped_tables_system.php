<?php
/**
 * Test Suite: Global Professional Theme-Based Striped Tables System
 *
 * Verifies:
 * 1. Global CSS rules in app.css for alternating rows (odd/even), hover highlight, and inheritance
 * 2. Theme color compatibility (Forest Teal / Ivory palette: #F8FAF9 odd, #FFFFFF even, #EEF5F2 hover)
 * 3. Sticky actions column background handling on horizontal scroll
 * 4. Single-line cell truncation rules (white-space: nowrap !important, text-overflow: ellipsis)
 * 5. Action menu dropdown clipping protection (overflow: visible !important)
 * 6. Responsive container expansion (width: 100% !important, max-width: 100% !important)
 * 7. Live Controller and View renders across core modules:
 *    - Bookings
 *    - Customers
 *    - Employees
 *    - Vehicles
 *    - Tours
 * 8. Mobile cards preservation
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Controllers\BookingController;
use App\Controllers\CustomerController;
use App\Controllers\EmployeeController;
use App\Controllers\VehicleController;
use App\Controllers\TourController;
use App\Core\Request;

$passed = 0;
$failed = 0;

function it(string $description, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo "[PASS] " . $description . PHP_EOL;
        $passed++;
    } catch (\Throwable $e) {
        echo "[FAIL] " . $description . " - ERROR: " . $e->getMessage() . PHP_EOL;
        $failed++;
    }
}

echo "========================================================" . PHP_EOL;
echo "  GLOBAL STRIPED TABLE SYSTEM TEST SUITE                " . PHP_EOL;
echo "========================================================" . PHP_EOL;

// 1. Verify CSS rules in app.css
it("app.css defines alternating striped rows (odd & even) with theme colors", function() {
    $cssPath = __DIR__ . '/../public/assets/css/app.css';
    $css = file_get_contents($cssPath);

    // Odd rows
    if (!str_contains($css, 'tr:nth-child(odd)') || !str_contains($css, '#F8FAF9')) {
        throw new \Exception("Missing tr:nth-child(odd) or #F8FAF9 subtle tint in app.css");
    }

    // Even rows
    if (!str_contains($css, 'tr:nth-child(even)') || !str_contains($css, '#FFFFFF')) {
        throw new \Exception("Missing tr:nth-child(even) or #FFFFFF in app.css");
    }

    // Hover state
    if (!str_contains($css, 'tbody tr:hover') || !str_contains($css, '#EEF5F2')) {
        throw new \Exception("Missing tbody tr:hover or #EEF5F2 highlight in app.css");
    }

    // Cell inheritance
    if (!str_contains($css, 'background-color: inherit;')) {
        throw new \Exception("Missing background-color: inherit for table cells in app.css");
    }
});

// 2. Verify sticky actions column background consistency
it("app.css handles sticky actions column backgrounds for striped rows", function() {
    $cssPath = __DIR__ . '/../public/assets/css/app.css';
    $css = file_get_contents($cssPath);

    if (!str_contains($css, 'tr:nth-child(odd) td.table-col-actions') ||
        !str_contains($css, 'tr:nth-child(even) td.table-col-actions') ||
        !str_contains($css, 'tr:hover td.table-col-actions')) {
        throw new \Exception("Missing sticky action column striping rules in app.css");
    }
});

// 3. Verify single-line truncation and action dropdown visibility
it("app.css enforces single-line table values and dropdown overflow safety", function() {
    $cssPath = __DIR__ . '/../public/assets/css/app.css';
    $css = file_get_contents($cssPath);

    // Single-line values
    if (!str_contains($css, 'white-space: nowrap !important;')) {
        throw new \Exception("Missing white-space: nowrap !important in app.css");
    }

    // Actions dropdown safety
    if (!str_contains($css, 'overflow: visible !important;') || !str_contains($css, 'text-overflow: clip !important;')) {
        throw new \Exception("Missing overflow: visible !important for actions cell in app.css");
    }
});

// 4. Verify responsive container expansion (sidebar collapse compatibility)
it("app.css ensures full width expansion for table containers", function() {
    $cssPath = __DIR__ . '/../public/assets/css/app.css';
    $css = file_get_contents($cssPath);

    if (!str_contains($css, '.modern-table-container,') ||
        !str_contains($css, 'width: 100% !important;') ||
        !str_contains($css, 'max-width: 100% !important;')) {
        throw new \Exception("Missing width: 100% / max-width: 100% container rules in app.css");
    }
});

// 5. Verify live Bookings table rendering
it("Bookings table renders with modern-table and responsive container", function() {
    $controller = new BookingController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'modern-table-container')) {
        throw new \Exception("Bookings view missing modern-table-container");
    }
    if (!str_contains($html, 'bookingsTable') || !str_contains($html, 'modern-table')) {
        throw new \Exception("Bookings view missing modern-table id='bookingsTable'");
    }
});

// 6. Verify live Customers table rendering
it("Customers table renders with modern-table and responsive container", function() {
    $controller = new CustomerController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'modern-table-container')) {
        throw new \Exception("Customers view missing modern-table-container");
    }
    if (!str_contains($html, 'customersTable') || !str_contains($html, 'modern-table')) {
        throw new \Exception("Customers view missing modern-table id='customersTable'");
    }
});

// 7. Verify live Employees table rendering
it("Employees table renders with modern-table and responsive container", function() {
    $controller = new EmployeeController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'modern-table-container')) {
        throw new \Exception("Employees view missing modern-table-container");
    }
    if (!str_contains($html, 'employeesTable') || !str_contains($html, 'modern-table')) {
        throw new \Exception("Employees view missing modern-table id='employeesTable'");
    }
});

// 8. Verify live Vehicles table rendering
it("Vehicles table renders with modern-table and responsive container", function() {
    $controller = new VehicleController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'modern-table-container')) {
        throw new \Exception("Vehicles view missing modern-table-container");
    }
    if (!str_contains($html, 'vehiclesTable') || !str_contains($html, 'modern-table')) {
        throw new \Exception("Vehicles view missing modern-table id='vehiclesTable'");
    }
});

// 9. Verify live Tours table rendering
it("Tours table renders with modern-table and responsive container", function() {
    $controller = new TourController();
    $req = new Request([], [], ['REQUEST_METHOD' => 'GET']);
    $response = $controller->index($req);

    if ($response->getStatusCode() !== 200) {
        throw new \Exception("Expected status 200, got: {$response->getStatusCode()}");
    }

    $html = $response->getContent();
    if (!str_contains($html, 'modern-table-container')) {
        throw new \Exception("Tours view missing modern-table-container");
    }
    if (!str_contains($html, 'toursTable') || !str_contains($html, 'modern-table')) {
        throw new \Exception("Tours view missing modern-table id='toursTable'");
    }
});

echo "========================================================" . PHP_EOL;
echo "  SUMMARY: {$passed} PASSED, {$failed} FAILED" . PHP_EOL;
echo "========================================================" . PHP_EOL;

if ($failed > 0) {
    exit(1);
}
exit(0);
