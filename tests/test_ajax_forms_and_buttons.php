<?php
/**
 * Test Suite: Global AJAX Form & Button Engine Verification
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Session;
use App\Core\Request;
use App\Core\Csrf;
use App\Controllers\VehicleController;
use App\Controllers\CustomerController;
use App\Controllers\DriverPortalController;

class AjaxFormsAndButtonsTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function assert(bool $condition, string $description): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✓ PASS: {$description}\n";
        } else {
            $this->failed++;
            echo "  ✗ FAIL: {$description}\n";
        }
    }

    public function run(): void
    {
        echo "\n======================================================\n";
        echo " RUNNING SUITE: Client Assets & AJAX Engine Verification\n";
        echo "======================================================\n";

        $jsPath = dirname(__DIR__) . '/public/assets/js/app.js';
        $this->assert(file_exists($jsPath), '1a. app.js exists');
        $js = file_get_contents($jsPath);

        $this->assert(str_contains($js, 'VoyageAjax ='), '1b. VoyageAjax engine defined in app.js');
        $this->assert(str_contains($js, 'setupTopProgressBar'), '1c. Top progress bar logic defined');
        $this->assert(str_contains($js, 'setupFormSubmissions'), '1d. Delegated form submissions handler defined');
        $this->assert(str_contains($js, 'setupLinkNavigation'), '1e. SPA link navigation handler defined');
        $this->assert(str_contains($js, 'setupActionButtons'), '1f. Action button handler defined');
        $this->assert(str_contains($js, 'setButtonLoading'), '1g. Button spinner and loading lock defined');
        $this->assert(str_contains($js, 'scanAndToastFlashes'), '1h. Flash message scanner and toast system defined');
        $this->assert(str_contains($js, 'VoyageAjax.init()'), '1i. VoyageAjax initialized on DOM ready');
        $this->assert(str_contains($js, 'window.vgToast'), '1j. window.vgToast exposed globally');

        $cssPath = dirname(__DIR__) . '/public/assets/css/app.css';
        $this->assert(file_exists($cssPath), '2a. app.css exists');
        $css = file_get_contents($cssPath);

        $this->assert(str_contains($css, '#vg-top-progress-bar'), '2b. app.css contains top progress bar styles');
        $this->assert(str_contains($css, '.vg-btn-spinner'), '2c. app.css contains button spinner styles');
        $this->assert(str_contains($css, '.is-loading'), '2d. app.css contains is-loading lock styles');
        $this->assert(str_contains($css, '.vg-page-fading'), '2e. app.css contains cross-fade transition styles');
        $this->assert(str_contains($css, '.vg-toast-container'), '2f. app.css contains toast container styles');
        $this->assert(str_contains($css, '.vg-toast'), '2g. app.css contains toast card styles');

        echo "\n======================================================\n";
        echo " RUNNING SUITE: AJAX Controller & Action Verification\n";
        echo "======================================================\n";

        // Generate CSRF token
        $csrfToken = Csrf::getToken();

        // 3. Test Vehicle Status AJAX Toggle
        Session::set('user_id', 1);
        Session::set('user_role', 'admin');
        Session::set('user', ['id' => 1, 'name' => 'Admin Officer', 'role' => 'admin']);

        $vehicleController = new VehicleController();
        $statusReq = new Request([], [
            'status' => 'maintenance',
            '_token' => $csrfToken
        ], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => $csrfToken
        ]);

        // Call update status on vehicle 1
        $statusRes = $vehicleController->updateStatus($statusReq, ['id' => 1]);
        $this->assert($statusRes->getStatusCode() === 302, '3a. Vehicle status toggle returns 302 redirect for client navigation');
        $hasFlash = !empty($_SESSION['_flash']) || Session::getFlash('success') !== null || Session::getFlash('danger') !== null;
        $this->assert($hasFlash, '3b. Session flash message set for toast feedback');

        // Reset status back to available
        $resetReq = new Request([], [
            'status' => 'available',
            '_token' => $csrfToken
        ], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ]);
        $vehicleController->updateStatus($resetReq, ['id' => 1]);

        // 4. Test Customer Filter GET Form via AJAX
        $customerController = new CustomerController();
        $filterReq = new Request([
            'search' => 'Sharma',
            'status' => 'active'
        ], [], [
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/office/customers?search=Sharma&status=active'
        ]);

        $filterRes = $customerController->index($filterReq);
        $this->assert($filterRes->getStatusCode() === 200, '4a. GET filter form via AJAX returns 200 OK');
        $content = $filterRes->getContent();
        $this->assert(str_contains($content, 'customers') || str_contains($content, 'Customer') || str_contains($content, 'table'), '4b. Filter response returns rendered table view');

        // 5. Test Driver Duty Status Toggle Form/Button via AJAX
        Session::set('user_id', 4);
        Session::set('user_role', 'driver');
        Session::set('user', ['id' => 4, 'name' => 'Rajesh Chauffeur', 'role' => 'driver']);

        $driverController = new DriverPortalController();
        $dutyReq = new Request([], [
            '_token' => $csrfToken
        ], [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_REFERER' => url('/driver/dashboard')
        ]);

        $dutyRes = $driverController->toggleDuty($dutyReq);
        $this->assert($dutyRes->getStatusCode() === 302, '5a. Duty toggle button returns 302 redirect to referer for in-place reload');
        $hasDutyFlash = !empty($_SESSION['_flash']) || Session::getFlash('success') !== null || Session::getFlash('danger') !== null;
        $this->assert($hasDutyFlash, '5b. Duty toggle flash notification set for toast display');

        echo "\n======================================================\n";
        echo " TEST EXECUTION SUMMARY\n";
        echo "======================================================\n";
        echo " Passed: {$this->passed}\n";
        echo " Failed: {$this->failed}\n";
        echo " Total:  " . ($this->passed + $this->failed) . "\n\n";

        if ($this->failed > 0) {
            echo " SOME TESTS FAILED.\n";
            exit(1);
        } else {
            echo " ALL AJAX FORMS AND BUTTONS TESTS PASSED!\n\n";
        }
    }
}

$test = new AjaxFormsAndButtonsTest();
$test->run();
