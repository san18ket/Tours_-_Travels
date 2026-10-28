<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$testName = "All Form Pages Consistency Suite (Matching customers/create)";
echo "======================================================\n";
echo " RUNNING SUITE: {$testName}\n";
echo "======================================================\n";

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $label): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failed++;
    }
}

$forms = [
    'customers/create' => dirname(__DIR__) . '/app/Views/office/customers/create.php',
    'vehicles/create'  => dirname(__DIR__) . '/app/Views/office/vehicles/create.php',
    'employees/create' => dirname(__DIR__) . '/app/Views/office/employees/create.php',
    'bookings/create'  => dirname(__DIR__) . '/app/Views/office/bookings/create.php',
    'users/create'     => dirname(__DIR__) . '/app/Views/office/users/create.php',
];

foreach ($forms as $pageName => $filePath) {
    echo "\n--- Testing {$pageName}.php ---\n";
    assertCondition(file_exists($filePath), "{$pageName}.php file exists");
    $content = file_get_contents($filePath);

    // 1. Centered stepper header
    assertCondition(strpos($content, 'class="modern-stepper-header-centered"') !== false, "{$pageName}: Centered stepper header is present");
    assertCondition(strpos($content, 'class="modern-stepper-container"') !== false, "{$pageName}: Stepper container is present");
    assertCondition(strpos($content, 'class="modern-stepper-track"') !== false, "{$pageName}: Stepper track is present");

    // 2. Removal of back buttons & admin buttons
    assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-back-btn/i', $content), "{$pageName}: Top bar back button element is absent");
    assertCondition(!preg_match('/<a[^>]*class="[^"]*create-topbar-admin-btn/i', $content), "{$pageName}: Top bar admin panel button element is absent");

    // 3. Single master modern-form-card wrapping panels (not duplicated inside every panel)
    assertCondition(strpos($content, 'class="modern-form-card"') !== false, "{$pageName}: Master .modern-form-card is present");
    assertCondition(substr_count($content, 'class="modern-form-card"') === 1, "{$pageName}: Exactly one master .modern-form-card wraps panels without nesting duplicates");

    // 4. Wizard panels exist
    assertCondition(strpos($content, 'class="modern-step-panel"') !== false, "{$pageName}: Modern step panels are present");

    // 5. Responsive CSS rules
    assertCondition(strpos($content, '.modern-stepper-header-centered') !== false, "{$pageName}: Stepper CSS rule defined");
    assertCondition(strpos($content, '@media (max-width: 480px)') !== false, "{$pageName}: Mobile @media (max-width: 480px) breakpoint defined");

    // 6. Full-width professional layout coverage (No excessive left/right empty whitespace)
    assertCondition(preg_match('/\.modern-form-wrapper\s*\{[^}]*width:\s*100%/s', $content) === 1, "{$pageName}: .modern-form-wrapper width: 100% defined");
    assertCondition(preg_match('/\.modern-stepper-container\s*\{[^}]*width:\s*100%/s', $content) === 1, "{$pageName}: .modern-stepper-container width: 100% defined");
    assertCondition(preg_match('/\.modern-stepper-container\s*\{[^}]*max-width:\s*100%/s', $content) === 1, "{$pageName}: .modern-stepper-container max-width: 100% defined");
    assertCondition(preg_match('/\.modern-stepper-track\s*\{[^}]*margin:\s*0 auto/s', $content) === 1, "{$pageName}: .modern-stepper-track centered with margin: 0 auto");
}

echo "\n--- Testing public/assets/css/app.css ---\n";
$appCssPath = dirname(__DIR__) . '/public/assets/css/app.css';
assertCondition(file_exists($appCssPath), "app.css file exists");
$appCss = file_get_contents($appCssPath);
assertCondition(preg_match('/\.modern-form-wrapper\s*\{[^}]*width:\s*100%/s', $appCss) === 1, "app.css: .modern-form-wrapper width: 100% defined");
assertCondition(preg_match('/\.modern-form-wrapper\s*\{[^}]*max-width:\s*100%/s', $appCss) === 1, "app.css: .modern-form-wrapper max-width: 100% defined");
assertCondition(preg_match('/\.modern-form-card\s*\{[^}]*width:\s*100%/s', $appCss) === 1, "app.css: .modern-form-card width: 100% defined");
assertCondition(preg_match('/\.modern-form-card\s*\{[^}]*max-width:\s*100%/s', $appCss) === 1, "app.css: .modern-form-card max-width: 100% defined");

echo "\n============================================\n";
echo "TEST RESULTS: Passed: {$passed}, Failed: {$failed}\n";
echo "============================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

