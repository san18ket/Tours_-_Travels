<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\RateLimiter;
use App\Core\ValidationException;
use App\Services\AuthService;
use Throwable;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(Request $request): Response
    {
        // Enterprise auto-creation: ensure database and core tables exist on login load
        if (\App\Core\MigrationManager::needsInitialization() || !\App\Core\MigrationManager::hasCoreTables()) {
            try {
                \App\Core\MigrationManager::autoInitialize();
            } catch (\Throwable $e) {
                \App\Core\Logger::warning("AuthController::showLogin auto-initialization: " . $e->getMessage());
            }
        }

        if (Session::isLoggedIn()) {
            return $this->redirectBasedOnRole(Session::getUserRole());
        }

        // Session-only authentication policy: revoke and clear any legacy persistent Remember Me cookie
        if (!empty($_COOKIE['tours_remember'])) {
            $this->authService->revokeRememberToken();
        }

        try {
            $response = $this->render('auth/login', [
                'title' => 'Sign In - ' . company_display_name()
            ], 'layouts/auth');
        } catch (\Throwable $e) {
            // Self-heal on missing tables during rendering
            if (str_contains($e->getMessage(), '42S02') || str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), "doesn't exist")) {
                \App\Core\MigrationManager::autoInitialize();
                $response = $this->render('auth/login', [
                    'title' => 'Sign In - ' . company_display_name()
                ], 'layouts/auth');
            } else {
                throw $e;
            }
        }

        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }

    public function login(Request $request): Response
    {
        // Enterprise auto-creation: ensure database and core tables exist before authentication
        if (\App\Core\MigrationManager::needsInitialization() || !\App\Core\MigrationManager::hasCoreTables()) {
            try {
                \App\Core\MigrationManager::autoInitialize();
            } catch (\Throwable $e) {
                \App\Core\Logger::warning("AuthController::login auto-initialization: " . $e->getMessage());
            }
        }

        try {
            $input = $request->all();
            $loginIdentifier = trim($input['login'] ?? $input['email'] ?? '');
            $password = (string)($input['password'] ?? '');

            if (empty($loginIdentifier)) {
                throw new ValidationException('Username or email address is required.');
            }
            if (empty($password)) {
                throw new ValidationException('Password is required.');
            }

            // Normal login creates a session-only login; persistent remember cookies are disabled
            $user = $this->authService->login($loginIdentifier, $password);

            // Clear login throttle key on success
            $ip = $request->getClientIp();
            RateLimiter::clear("throttle:login:{$ip}:" . hash('sha256', strtolower($loginIdentifier)));

            $targetUrl = ($user['role'] === 'driver') ? url('/driver/dashboard') : url('/office/dashboard');

            if ($request->isAjax()) {
                return $this->json([
                    'success'  => true,
                    'message'  => "Welcome back, {$user['name']}!",
                    'redirect' => $targetUrl,
                ]);
            }

            return $this->redirect($targetUrl);
        } catch (ValidationException $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/login'));
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 401);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/login'));
        }
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout();
        return $this->redirect(url('/login'));
    }

    private function redirectBasedOnRole(?string $role): Response
    {
        switch ($role) {
            case 'driver':
                return $this->redirect(url('/driver/dashboard'));
            case 'admin':
            case 'dispatcher':
                return $this->redirect(url('/office/dashboard'));
            case 'customer':
            default:
                return $this->redirect(url('/office/bookings'));
        }
    }
}
