<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$passed = 0;
$failed = 0;

function it(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  \033[32m✓ PASS:\033[0m {$desc}\n";
    } else {
        $failed++;
        echo "  \033[31m✗ FAIL:\033[0m {$desc}\n";
    }
}

function suite(string $name): void {
    echo "\n======================================================\n";
    echo " RUNNING SUITE: {$name}\n";
    echo "======================================================\n";
}

// =========================================================================
// SUITE 1: CSS Red Border Tokens & Micro-Animations
// =========================================================================
suite("Professional Red Border CSS Styling in app.css");

$appCss = file_get_contents(__DIR__ . '/../public/assets/css/app.css');

it("1a. CSS defines .is-invalid and .vg-input-error selector", 
    str_contains($appCss, '.is-invalid') && str_contains($appCss, '.vg-input-error'));

it("1b. CSS applies red border #EF4444 to invalid inputs", 
    str_contains($appCss, 'border-color: #EF4444 !important;'));

it("1c. CSS applies soft red glow box-shadow (rgba(239, 68, 68, 0.16))", 
    str_contains($appCss, 'rgba(239, 68, 68, 0.16)'));

it("1d. CSS defines active focus red border #DC2626", 
    str_contains($appCss, 'border-color: #DC2626 !important;'));

it("1e. CSS defines @keyframes vgInputShake tactile animation", 
    str_contains($appCss, '@keyframes vgInputShake') && str_contains($appCss, '.vg-input-shake'));

it("1f. CSS defines smooth transition for border-color and box-shadow", 
    str_contains($appCss, 'transition: border-color 0.18s ease, box-shadow 0.18s ease'));


// =========================================================================
// SUITE 2: VoyageValidation Engine in app.js
// =========================================================================
suite("VoyageValidation JavaScript Engine in app.js");

$appJs = file_get_contents(__DIR__ . '/../public/assets/js/app.js');

it("2a. app.js exports window.VoyageValidation", 
    str_contains($appJs, 'window.VoyageValidation = VoyageValidation;'));

it("2b. VoyageValidation defines markError method with red border and shadow", 
    str_contains($appJs, 'markError: function(input)') && 
    str_contains($appJs, "input.style.borderColor = '#EF4444';") &&
    str_contains($appJs, "input.classList.add('is-invalid', 'vg-input-error');"));

it("2c. VoyageValidation defines clearError method removing red border", 
    str_contains($appJs, 'clearError: function(input)') && 
    str_contains($appJs, "input.classList.remove('is-invalid', 'vg-input-error'") &&
    str_contains($appJs, "input.style.borderColor = '';"));

it("2d. VoyageValidation defines isInputFilled checking value length and selection", 
    str_contains($appJs, 'isInputFilled: function(input)') &&
    str_contains($appJs, 'input.value.trim().length > 0'));

it("2e. VoyageValidation binds live input/change/keyup listeners to auto-remove red border", 
    str_contains($appJs, 'bindClearListener: function(input)') &&
    str_contains($appJs, "input.addEventListener('input'") &&
    str_contains($appJs, "input.addEventListener('change'"));

it("2f. VoyageValidation defines validateContainer for multi-step wizards", 
    str_contains($appJs, 'validateContainer: function(container)'));

it("2g. VoyageValidation defines validateForm for form saves and submits", 
    str_contains($appJs, 'validateForm: function(form)'));

it("2h. VoyageValidation applies novalidate globally to forms to suppress browser alert popups", 
    str_contains($appJs, "form.setAttribute('novalidate', 'true')"));

it("2i. VoyageValidation intercepts form submit globally and halts if required fields are missing", 
    str_contains($appJs, 'if (!this.validateForm(form))') &&
    str_contains($appJs, 'e.preventDefault()') &&
    str_contains($appJs, 'e.stopPropagation()'));

it("2j. VoyageAjax.setupFormSubmissions delegates validation to VoyageValidation without reportValidity", 
    str_contains($appJs, 'if (window.VoyageValidation && !window.VoyageValidation.validateForm(form))') &&
    !str_contains($appJs, 'form.reportValidity()'));

it("2k. VoyageValidation is initialized inside initVoyageGoApp", 
    str_contains($appJs, 'VoyageValidation.init();'));


// =========================================================================
// SUITE 3: Elimination of alert() & reportValidity() in View Scripts
// =========================================================================
suite("Elimination of Alert Popups and Integration Across Views");

$custCreate = file_get_contents(__DIR__ . '/../app/Views/office/customers/create.php');
$usrCreate  = file_get_contents(__DIR__ . '/../app/Views/office/users/create.php');
$empCreate  = file_get_contents(__DIR__ . '/../app/Views/office/employees/create.php');
$rolesIdx   = file_get_contents(__DIR__ . '/../app/Views/office/roles/index.php');
$assignPerm = file_get_contents(__DIR__ . '/../app/Views/office/role_permissions/assign.php');

it("3a. customers/create.php no longer calls reportValidity()", 
    !str_contains($custCreate, 'firstInvalid.reportValidity()') && !str_contains($custCreate, 'reportValidity'));

it("3b. customers/create.php delegates wizard validation to VoyageValidation", 
    str_contains($custCreate, 'window.VoyageValidation.validateContainer(panel)'));

it("3c. users/create.php no longer calls reportValidity()", 
    !str_contains($usrCreate, 'reportValidity'));

it("3d. users/create.php no longer calls alert() on password mismatch", 
    !str_contains($usrCreate, 'alert('));

it("3e. users/create.php uses VoyageValidation.markError on invalid inputs", 
    str_contains($usrCreate, 'window.VoyageValidation.markError'));

it("3f. employees/create.php no longer calls alert() on Step 1, 2 or 3 inputs", 
    !str_contains($empCreate, 'alert('));

it("3g. employees/create.php uses VoyageValidation.markError on missing fields", 
    str_contains($empCreate, 'window.VoyageValidation.markError(phone)') &&
    str_contains($empCreate, 'window.VoyageValidation.markError(firstName)') &&
    str_contains($empCreate, 'window.VoyageValidation.markError(lastName)'));

it("3h. roles/index.php validateRoleForm no longer calls alert()", 
    !str_contains($rolesIdx, "alert('Please enter a role name.')") && !str_contains($rolesIdx, 'alert('));

it("3i. roles/index.php uses VoyageValidation.markError on roleNameInput", 
    str_contains($rolesIdx, 'window.VoyageValidation.markError(nameInput)'));

it("3j. role_permissions/assign.php no longer calls alert()", 
    !str_contains($assignPerm, 'alert('));


// =========================================================================
// TEST SUMMARY
// =========================================================================
echo "\n======================================================\n";
echo " TEST EXECUTION SUMMARY\n";
echo "======================================================\n";
echo " Passed: {$passed}\n";
echo " Failed: {$failed}\n";
echo " Total:  " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo " \033[32mALL RED BORDER VALIDATION TESTS PASSED!\033[0m\n\n";
    exit(0);
} else {
    echo " \033[31mSOME TESTS FAILED!\033[0m\n\n";
    exit(1);
}
