<?php
/**
 * Application Routes Definition — VoyageGo Luxury Transport & Chauffeur Suite
 * Final Approved Scope: Exactly 8 Business Modules + Driver Portal & Telemetry APIs
 */

use App\Core\Router;
use App\Core\Response;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TourController;
use App\Controllers\BookingController;
use App\Controllers\FleetController;
use App\Controllers\VehicleController;
use App\Controllers\EmployeeController;
use App\Controllers\DispatchController;
use App\Controllers\InvoiceController;
use App\Controllers\FinanceController;
use App\Controllers\DriverPortalController;
use App\Controllers\SetupController;
use App\Controllers\BackupController;
use App\Controllers\ApiController;
use App\Controllers\UserController;
use App\Controllers\CustomerController;
use App\Controllers\RoleController;
use App\Controllers\MobileApiController;
use App\Controllers\LanguageController;
use App\Controllers\CompanyController;
use App\Controllers\EmailOtpController;
use App\Controllers\AccountRecoveryController;
use App\Controllers\EmailController;

$router = new Router();

// -----------------------------------------------------------------------------
// Public & Home Redirection
// -----------------------------------------------------------------------------
$router->get('/', function ($request) {
    if (Session::isLoggedIn()) {
        $role = Session::getUserRole();
        if ($role === 'driver') {
            return Response::redirect(url('/driver/dashboard'));
        }
        return Response::redirect(url('/office/dashboard'));
    }
    return Response::redirect(url('/login'));
});

$router->get('/admin', function ($request) {
    return Response::redirect(url('/office/dashboard'));
});

$router->get('/dashboard', function ($request) {
    return Response::redirect(url('/office/dashboard'));
});

$router->get('/customers/create', function ($request) {
    return Response::redirect(url('/office/customers/create'));
});

$router->get('/customers', function ($request) {
    return Response::redirect(url('/office/customers'));
});

// -----------------------------------------------------------------------------
// Language Selection & Persistence
// -----------------------------------------------------------------------------
$router->post('/language/set', [LanguageController::class, 'setLanguage'], ['csrf']);

// -----------------------------------------------------------------------------
// Authentication
// -----------------------------------------------------------------------------
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], ['csrf', 'throttle:login']);
$router->post('/logout', [AuthController::class, 'logout'], ['csrf']);

// -----------------------------------------------------------------------------
// Account Recovery (Forgot Username & Forgot Password with 4-Digit Email OTP)
// -----------------------------------------------------------------------------
$router->get('/forgot-account', [AccountRecoveryController::class, 'showRecoveryHub']);

// Option A: Forgot Username
$router->get('/forgot-username', [AccountRecoveryController::class, 'showForgotUsername']);
$router->post('/forgot-username/send-otp', [AccountRecoveryController::class, 'sendUsernameOtp'], ['csrf', 'throttle:api']);
$router->post('/forgot-username/verify-otp', [AccountRecoveryController::class, 'verifyUsernameOtp'], ['csrf']);
$router->post('/forgot-username/recover', [AccountRecoveryController::class, 'recoverUsername'], ['csrf']);

// Option B: Forgot Password
$router->get('/forgot-password', [AccountRecoveryController::class, 'showForgotPassword']);
$router->post('/forgot-password/send-otp', [AccountRecoveryController::class, 'sendPasswordOtp'], ['csrf', 'throttle:api']);
$router->post('/forgot-password/verify-otp', [AccountRecoveryController::class, 'verifyPasswordOtp'], ['csrf']);
$router->get('/forgot-password/reset', [AccountRecoveryController::class, 'showResetPassword']);
$router->post('/forgot-password/reset', [AccountRecoveryController::class, 'resetPassword'], ['csrf']);

// -----------------------------------------------------------------------------
// Public Email OTP Verification Endpoints (Rate limited, CSRF protected)
// -----------------------------------------------------------------------------
$router->post('/email-otp/send', [EmailOtpController::class, 'sendOtp'], ['csrf', 'throttle:api']);
$router->post('/email-otp/resend', [EmailOtpController::class, 'resendOtp'], ['csrf', 'throttle:api']);
$router->post('/email-otp/verify', [EmailOtpController::class, 'verifyOtp'], ['csrf']);
$router->post('/email-otp/invalidate', [EmailOtpController::class, 'invalidate'], ['csrf']);

// -----------------------------------------------------------------------------
// Setup Wizard (Database Initialization & Migrations)
// -----------------------------------------------------------------------------
$router->get('/setup', [SetupController::class, 'index']);
$router->post('/setup/run', [SetupController::class, 'run'], ['csrf']);

// -----------------------------------------------------------------------------
// MODULE 1: Dashboard (Office Command Center)
// -----------------------------------------------------------------------------
$router->get('/office/dashboard', [DashboardController::class, 'index'], ['auth', 'role:admin,dispatcher']);

// -----------------------------------------------------------------------------
// MODULE 2: Customers — Add Customer and Customer List
// -----------------------------------------------------------------------------
$router->get('/office/customers', [CustomerController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/customers/create', [CustomerController::class, 'create'], ['auth', 'role:admin']);
$router->post('/office/customers', [CustomerController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/customers/{id}', [CustomerController::class, 'show'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/customers/{id}/edit', [CustomerController::class, 'edit'], ['auth', 'role:admin']);
$router->post('/office/customers/{id}', [CustomerController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/customers/{id}/status', [CustomerController::class, 'toggleStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/customers/{id}/delete', [CustomerController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// -----------------------------------------------------------------------------
// Email OTP Verification Endpoints (Reusable across Customer & Employee Creation)
// -----------------------------------------------------------------------------
$router->post('/office/email-otp/send', [EmailOtpController::class, 'sendOtp'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/email-otp/resend', [EmailOtpController::class, 'resendOtp'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/email-otp/verify', [EmailOtpController::class, 'verifyOtp'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/email-otp/invalidate', [EmailOtpController::class, 'invalidate'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 3: Bookings — Create Booking and Booking List
// -----------------------------------------------------------------------------
$router->get('/office/bookings', [BookingController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/bookings/create', [BookingController::class, 'create'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/bookings', [BookingController::class, 'store'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/bookings/{id}', [BookingController::class, 'show'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/bookings/{id}/status', [BookingController::class, 'updateStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/bookings/{id}/toggle-status', [BookingController::class, 'toggleStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/bookings/{id}/delete', [BookingController::class, 'delete'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 4: Employees — Add Employee and Employee List (Admin Only)
// -----------------------------------------------------------------------------
$router->get('/office/employees', [EmployeeController::class, 'index'], ['auth', 'role:admin']);
$router->get('/office/employees/create', [EmployeeController::class, 'create'], ['auth', 'role:admin']);
$router->post('/office/employees/create', [EmployeeController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/employees', [EmployeeController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/employees/{id}', [EmployeeController::class, 'show'], ['auth', 'role:admin']);
$router->get('/office/employees/{id}/edit', [EmployeeController::class, 'edit'], ['auth', 'role:admin']);
$router->post('/office/employees/{id}', [EmployeeController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/employees/{id}/delete', [EmployeeController::class, 'delete'], ['auth', 'role:admin', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 5: Vehicles — Add Vehicle and Vehicle List (Admin & Dispatcher)
// -----------------------------------------------------------------------------
$router->get('/office/vehicles', [VehicleController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/vehicles/create', [VehicleController::class, 'create'], ['auth', 'role:admin']);
$router->post('/office/vehicles', [VehicleController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/vehicles/{id}/status', [VehicleController::class, 'updateStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/vehicles/{id}/toggle-status', [VehicleController::class, 'toggleStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/vehicles/{id}/delete', [VehicleController::class, 'delete'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 6: Access Control — Add User, User List, Roles & Permissions (Admin Only)
// -----------------------------------------------------------------------------
$router->get('/office/users', [UserController::class, 'index'], ['auth', 'role:admin']);
$router->get('/office/users/create', [UserController::class, 'create'], ['auth', 'role:admin']);
$router->post('/office/users/create', [UserController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/users', [UserController::class, 'store'], ['auth', 'role:admin', 'csrf']);
// Role and Permission Management (Admin Only)
$router->get('/office/roles', [RoleController::class, 'index'], ['auth', 'role:admin']);
$router->post('/office/roles', [RoleController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/roles/{id}/update', [RoleController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/roles/{id}/delete', [RoleController::class, 'destroy'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/role-permission', [RoleController::class, 'permissionIndex'], ['auth', 'role:admin']);
$router->get('/office/role-permission/assign', [RoleController::class, 'permissionCreate'], ['auth', 'role:admin']);
$router->get('/office/role-permission/options/{id}', [RoleController::class, 'permissionOptions'], ['auth', 'role:admin']);
$router->get('/office/role-permission/user-options/{id}', [RoleController::class, 'userPermissionOptions'], ['auth', 'role:admin']);
$router->post('/office/role-permission', [RoleController::class, 'permissionStore'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/users/roles', [RoleController::class, 'permissionCreate'], ['auth', 'role:admin']);
$router->get('/office/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'role:admin']);
$router->post('/office/users/{id}', [UserController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/users/{id}/toggle-status', [UserController::class, 'toggleStatus'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/users/{id}/reset-password', [UserController::class, 'resetPassword'], ['auth', 'role:admin', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 7: Trip Operations — Assign Trip to Driver and Trip History (Admin & Dispatcher)
// -----------------------------------------------------------------------------
$router->get('/office/trips/assign', [DispatchController::class, 'createAssign'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/trips/assign', [DispatchController::class, 'assign'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/trips/history', [DispatchController::class, 'history'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/trips/{id}/cancel', [DispatchController::class, 'cancel'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/trips/{id}/toggle-status', [DispatchController::class, 'toggleStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 8: Live Tracking — Real-Time Fleet Map (Admin & Dispatcher)
// -----------------------------------------------------------------------------
$router->get('/office/tracking', [DispatchController::class, 'liveTracking'], ['auth', 'role:admin,dispatcher']);

// -----------------------------------------------------------------------------
// Graceful Legacy Route Redirections & Compatibility
// -----------------------------------------------------------------------------
$router->get('/office/tours', function ($request) {
    return Response::redirect(url('/office/bookings'));
}, ['auth', 'role:admin,dispatcher']);
$router->get('/office/tours/create', function ($request) {
    return Response::redirect(url('/office/bookings/create'));
}, ['auth', 'role:admin']);

$router->get('/office/fleet', function ($request) {
    return Response::redirect(url('/office/vehicles'));
}, ['auth', 'role:admin,dispatcher']);
$router->get('/office/fleet/vehicles/create', function ($request) {
    return Response::redirect(url('/office/vehicles/create'));
}, ['auth', 'role:admin']);
$router->post('/office/fleet/vehicles', [VehicleController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/fleet/vehicles/{id}/status', [VehicleController::class, 'updateStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);

$router->get('/office/dispatch', function ($request) {
    return Response::redirect(url('/office/trips/assign'));
}, ['auth', 'role:admin,dispatcher']);
$router->post('/office/dispatch/assign', [DispatchController::class, 'assign'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// Preserved Billing & Financial Compliance Records (Retained, No Dropped Tables)
// -----------------------------------------------------------------------------
$router->get('/office/invoices', [InvoiceController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/invoices/{id}', [InvoiceController::class, 'show'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/invoices/{id}/print', [InvoiceController::class, 'printInvoice'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/invoices/generate/{booking_id}', [InvoiceController::class, 'generate'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/invoices/{id}/payment', [InvoiceController::class, 'recordPayment'], ['auth', 'role:admin,dispatcher', 'csrf']);

// -----------------------------------------------------------------------------
// Finance & Treasury Operations
// -----------------------------------------------------------------------------
$router->get('/office/finance', [FinanceController::class, 'dashboard'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/dashboard', [FinanceController::class, 'dashboard'], ['auth', 'role:admin,dispatcher']);

// Customer Payments
$router->get('/office/finance/payments', [FinanceController::class, 'payments'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/pending-payments', [FinanceController::class, 'pendingPayments'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/refunds', [FinanceController::class, 'refunds'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/refunds', [FinanceController::class, 'storeRefund'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/refunds/{id}/status', [FinanceController::class, 'updateRefundStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);

// Business Expenses
$router->get('/office/finance/expenses', [FinanceController::class, 'expenses'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/expenses', [FinanceController::class, 'storeExpense'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/expenses/{id}/status', [FinanceController::class, 'updateExpenseStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/expense-categories', [FinanceController::class, 'expenseCategories'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/expense-categories', [FinanceController::class, 'storeExpenseCategory'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/expense-categories/{id}', [FinanceController::class, 'updateExpenseCategory'], ['auth', 'role:admin,dispatcher', 'csrf']);

// Staff Salary & Compensation
$router->get('/office/finance/payroll', [FinanceController::class, 'payroll'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/payroll/generate', [FinanceController::class, 'generatePayroll'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/payroll/{id}', [FinanceController::class, 'payrollShow'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/payroll/{id}/pay', [FinanceController::class, 'markPayrollPaid'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/payslips', [FinanceController::class, 'payslips'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/payslips/{id}', [FinanceController::class, 'payslip'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/payslips/{id}/pay', [FinanceController::class, 'markPayslipPaid'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/salary-history', [FinanceController::class, 'salaryHistory'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/advances', [FinanceController::class, 'advances'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/advances', [FinanceController::class, 'storeAdvance'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/advances/{id}/status', [FinanceController::class, 'updateAdvanceStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/advances/{id}', [FinanceController::class, 'showAdvance'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/advances/{id}', [FinanceController::class, 'updateAdvance'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/advances/{id}/delete', [FinanceController::class, 'deleteAdvance'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/advances/{id}/toggle-status', [FinanceController::class, 'toggleAdvanceStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);

// Cash & Bank
$router->get('/office/finance/accounts', [FinanceController::class, 'accounts'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/accounts', [FinanceController::class, 'storeAccount'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/accounts/{id}', [FinanceController::class, 'accountShow'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/accounts/{id}', [FinanceController::class, 'updateAccount'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/accounts/{id}/toggle-status', [FinanceController::class, 'toggleAccountStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/account-details', [FinanceController::class, 'accountDetailsDirect'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/transactions', [FinanceController::class, 'transactions'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/transactions', [FinanceController::class, 'recordTransaction'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/transactions/{id}', [FinanceController::class, 'showTransaction'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/transactions/{id}', [FinanceController::class, 'updateTransaction'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/transactions/{id}/delete', [FinanceController::class, 'deleteTransaction'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/finance/transactions/{id}/toggle-status', [FinanceController::class, 'toggleTransactionStatus'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/finance/transaction-details', [FinanceController::class, 'transactionDetailsDirect'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/finance/transfer', [FinanceController::class, 'transfer'], ['auth', 'role:admin,dispatcher', 'csrf']);

// Financial Reports
$router->get('/office/finance/reports', [FinanceController::class, 'reports'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/income', [FinanceController::class, 'incomeReport'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/expenses', [FinanceController::class, 'expenseReport'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/profit-loss', [FinanceController::class, 'profitLossReport'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/customer-dues', [FinanceController::class, 'customerDuesReport'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/salary', [FinanceController::class, 'salaryReport'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/finance/reports/expense-categories', [FinanceController::class, 'expenseCategoryReport'], ['auth', 'role:admin,dispatcher']);

// -----------------------------------------------------------------------------
// Backups & Disaster Recovery (Admin Only)
// -----------------------------------------------------------------------------
$router->get('/office/backups', [BackupController::class, 'index'], ['auth', 'role:admin']);
$router->post('/office/backups/create', [BackupController::class, 'create'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/backups/restore', [BackupController::class, 'restore'], ['auth', 'role:admin', 'csrf']);

// -----------------------------------------------------------------------------
// Single Company Profile Management (Admin Only)
// -----------------------------------------------------------------------------
$router->get('/office/company', [CompanyController::class, 'index'], ['auth', 'role:admin']);
$router->post('/office/company', [CompanyController::class, 'save'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/company/save', [CompanyController::class, 'save'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/company/create', [CompanyController::class, 'create'], ['auth', 'role:admin']);
$router->post('/office/company/create', [CompanyController::class, 'store'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/company/{id}/edit', [CompanyController::class, 'edit'], ['auth', 'role:admin']);
$router->post('/office/company/{id}', [CompanyController::class, 'update'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/company/remove-image', [CompanyController::class, 'removeImage'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/company/remove-logo', [CompanyController::class, 'removeLogo'], ['auth', 'role:admin', 'csrf']);

// -----------------------------------------------------------------------------
// MODULE 19: Dedicated Driver Portal — Role-Based UI, Sidebar, Navbar & Pages
// -----------------------------------------------------------------------------
$router->get('/driver', function ($request) {
    return Response::redirect(url('/driver/dashboard'));
}, ['auth', 'role:driver']);
$router->get('/driver/dashboard', [DriverPortalController::class, 'dashboard'], ['auth', 'role:driver']);
$router->get('/driver/trips', [DriverPortalController::class, 'assignedTrips'], ['auth', 'role:driver']);
$router->get('/driver/trips/{id}', [DriverPortalController::class, 'tripDetails'], ['auth', 'role:driver']);
$router->post('/driver/trips/{id}/accept', [DriverPortalController::class, 'acceptTrip'], ['auth', 'role:driver', 'csrf']);
$router->post('/driver/trips/{id}/start', [DriverPortalController::class, 'startTrip'], ['auth', 'role:driver', 'csrf']);
$router->post('/driver/trips/{id}/complete', [DriverPortalController::class, 'completeTrip'], ['auth', 'role:driver', 'csrf']);
$router->get('/driver/history', [DriverPortalController::class, 'history'], ['auth', 'role:driver']);
$router->get('/driver/vehicle', [DriverPortalController::class, 'vehicle'], ['auth', 'role:driver']);
$router->get('/driver/location', [DriverPortalController::class, 'location'], ['auth', 'role:driver']);
$router->get('/driver/profile', [DriverPortalController::class, 'profile'], ['auth', 'role:driver']);
$router->post('/driver/profile', [DriverPortalController::class, 'updateProfile'], ['auth', 'role:driver', 'csrf']);
$router->post('/driver/profile/password', [DriverPortalController::class, 'updatePassword'], ['auth', 'role:driver', 'csrf']);
$router->get('/driver/notifications', [DriverPortalController::class, 'notificationsPage'], ['auth', 'role:driver']);
$router->post('/driver/duty/toggle', [DriverPortalController::class, 'toggleDuty'], ['auth', 'role:driver', 'csrf']);
$router->post('/driver/notifications/{id}/read', [DriverPortalController::class, 'markNotificationRead'], ['auth', 'role:driver', 'csrf']);
$router->post('/driver/notifications/mark-all-read', [DriverPortalController::class, 'markAllNotificationsRead'], ['auth', 'role:driver', 'csrf']);
$router->get('/driver/duty', function ($request) {
    return Response::redirect(url('/driver/dashboard'));
}, ['auth', 'role:driver']);

// -----------------------------------------------------------------------------
// Telemetry & Sync APIs
// -----------------------------------------------------------------------------
$router->post('/api/gps/update', [ApiController::class, 'updateGps'], ['auth', 'role:driver', 'csrf', 'throttle:gps']);
$router->get('/api/fleet/live', [ApiController::class, 'fleetLive'], ['auth', 'role:admin,dispatcher', 'throttle:api']);
$router->get('/api/sync/status', [ApiController::class, 'syncStatus']);

// -----------------------------------------------------------------------------
// Versioned Mobile Driver API (Native Background Tracking & Offline Sync)
// -----------------------------------------------------------------------------
$router->post('/api/mobile/v1/auth/login', [MobileApiController::class, 'login'], ['throttle:login']);
$router->post('/api/mobile/v1/auth/refresh', [MobileApiController::class, 'refresh'], ['throttle:api']);
$router->post('/api/mobile/v1/auth/logout', [MobileApiController::class, 'logout'], ['mobile_auth']);
$router->get('/api/mobile/v1/driver/me', [MobileApiController::class, 'me'], ['mobile_auth']);
$router->get('/api/mobile/v1/driver/trips', [MobileApiController::class, 'trips'], ['mobile_auth']);
$router->get('/api/mobile/v1/driver/notifications', [MobileApiController::class, 'notifications'], ['mobile_auth']);
$router->get('/api/mobile/v1/tracking/status', [MobileApiController::class, 'trackingStatus'], ['mobile_auth']);
$router->post('/api/mobile/v1/tracking/start', [MobileApiController::class, 'trackingStart'], ['mobile_auth']);
$router->post('/api/mobile/v1/tracking/stop', [MobileApiController::class, 'trackingStop'], ['mobile_auth']);
$router->post('/api/mobile/v1/gps', [MobileApiController::class, 'syncGps'], ['mobile_auth', 'throttle:gps']);

// -----------------------------------------------------------------------------
// Professional Centralized Email System (Office & Deliverability Management)
// -----------------------------------------------------------------------------
$router->get('/office/emails', [EmailController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/emails/history', [EmailController::class, 'history'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/emails/details/{id}', [EmailController::class, 'details'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/emails/retry/{id}', [EmailController::class, 'retry'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/emails/settings', [EmailController::class, 'settings'], ['auth', 'role:admin']);
$router->post('/office/emails/test-smtp', [EmailController::class, 'testSmtp'], ['auth', 'role:admin', 'csrf']);
$router->post('/office/emails/send-test', [EmailController::class, 'sendTest'], ['auth', 'role:admin', 'csrf']);
$router->get('/office/emails/preview', [EmailController::class, 'preview'], ['auth', 'role:admin,dispatcher']);
$router->get('/office/emails/preview/render', [EmailController::class, 'previewRender'], ['auth', 'role:admin,dispatcher']);

// Backward-compatible routes mapping to centralized EmailController
$router->get('/office/notification-settings', [EmailController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/notification-settings', [EmailController::class, 'sendTest'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/email/test', [EmailController::class, 'index'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/email/test', [EmailController::class, 'sendTest'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/email/retry/{id}', [EmailController::class, 'retry'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/office/email/details/{id}', [EmailController::class, 'details'], ['auth', 'role:admin,dispatcher']);
$router->post('/office/customers/{id}/resend-welcome', [CustomerController::class, 'resendWelcome'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->post('/office/employees/{id}/resend-welcome', [EmployeeController::class, 'resendWelcome'], ['auth', 'role:admin,dispatcher', 'csrf']);
$router->get('/driver/notification-settings', [EmailController::class, 'index'], ['auth', 'role:driver']);

return $router;
