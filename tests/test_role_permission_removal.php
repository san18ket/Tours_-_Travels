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
// SUITE 1: Layout Sidebar Verification
// =========================================================================
suite("Removal of Roles & Permission Matrix from Sidebar");

$officeLayout = file_get_contents(__DIR__ . '/../app/Views/layouts/office.php');

it("1a. Office layout sidebar does NOT contain /office/roles link", 
    !str_contains($officeLayout, 'href="<?= url(\'/office/roles\') ?>"') &&
    !str_contains($officeLayout, 'href="/office/roles"'));

it("1b. Office layout sidebar does NOT contain /office/role-permission link", 
    !str_contains($officeLayout, 'href="<?= url(\'/office/role-permission\') ?>"') &&
    !str_contains($officeLayout, 'href="/office/role-permission"'));

it("1c. Office layout Category 6 title is simplified to Users", 
    str_contains($officeLayout, 'id="cat-access"') && 
    str_contains($officeLayout, 'data-title="User Accounts"'));

it("1d. Office layout preserves clean Users link", 
    str_contains($officeLayout, 'url(\'/office/users\')'));

it("1e. Office layout preserves clean Create User link", 
    str_contains($officeLayout, 'url(\'/office/users/create\')'));


// =========================================================================
// SUITE 2: Routes Redirects Verification
// =========================================================================
suite("Routing Cleanups & Graceful Redirects");

$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');

it("2a. routes.php contains /office/roles redirect", 
    str_contains($routesContent, "'/office/roles'") && str_contains($routesContent, "Response::redirect"));

it("2b. routes.php contains /office/role-permission redirect", 
    str_contains($routesContent, "'/office/role-permission'") && str_contains($routesContent, "Response::redirect"));

\App\Core\Session::start();
\App\Core\Session::setUser(['id' => 1, 'role' => 'admin', 'status' => 'active']);
require __DIR__ . '/../app/routes.php';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/office/roles';
$rolesResponse = $router->dispatch(new \App\Core\Request());
it("2c. GET /office/roles returns HTTP 302 redirect without exception", $rolesResponse->getStatusCode() === 302);
it("2d. GET /office/roles redirects to /office/users", str_contains($rolesResponse->getHeaders()['Location'] ?? '', '/office/users'));

$_SERVER['REQUEST_URI'] = '/office/role-permission';
$permResponse = $router->dispatch(new \App\Core\Request());
it("2e. GET /office/role-permission returns HTTP 302 redirect without exception", $permResponse->getStatusCode() === 302);
it("2f. GET /office/role-permission redirects to /office/users", str_contains($permResponse->getHeaders()['Location'] ?? '', '/office/users'));


// =========================================================================
// SUITE 3: Default Driver Role in Forms
// =========================================================================
suite("Default Driver Role Configuration Across Views");

$userCreate = file_get_contents(__DIR__ . '/../app/Views/office/users/create.php');
$empCreate  = file_get_contents(__DIR__ . '/../app/Views/office/employees/create.php');

it("3a. users/create.php does NOT contain + Add Role shortcut link", 
    !str_contains($userCreate, 'url(\'/office/roles\')'));

it("3b. users/create.php role select defaults to driver role", 
    str_contains($userCreate, '<option value="driver" selected>'));

it("3c. employees/create.php does NOT contain + Add Role shortcut link", 
    !str_contains($empCreate, 'url(\'/office/roles\')'));

it("3d. employees/create.php role select defaults to driver role", 
    str_contains($empCreate, '<option value="driver" selected'));


// =========================================================================
// SUITE 4: Controller Default Role Fallback
// =========================================================================
suite("Controllers Default Role Assignment");

$userCtrl = file_get_contents(__DIR__ . '/../app/Controllers/UserController.php');
$empCtrl  = file_get_contents(__DIR__ . '/../app/Controllers/EmployeeController.php');

it("4a. UserController defaults role to 'driver' when empty", 
    str_contains($userCtrl, "\$input['role'] = 'driver'"));

it("4b. EmployeeController defaults role to 'driver' when empty", 
    str_contains($empCtrl, "\$input['role'] = 'driver'"));


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
    echo " \033[32mALL ROLES PERMISSION REMOVAL TESTS PASSED!\033[0m\n\n";
    exit(0);
} else {
    echo " \033[31mSOME TESTS FAILED!\033[0m\n\n";
    exit(1);
}
