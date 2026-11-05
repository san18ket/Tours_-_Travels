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
        if (Session::isLoggedIn()) {
            return $this->redirectBasedOnRole(Session::getUserRole());
        }

        // Check if persistent Remember Me token is present
        $rememberedUser = $this->authService->attemptRememberMeLogin();
        if ($rememberedUser) {
            return $this->redirectBasedOnRole(Session::getUserRole());
        }

        $response = $this->render('auth/login', [
            'title' => 'Sign In - ' . company_display_name()
        ], 'layouts/auth');

        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }

    public function login(Request $request): Response
    {
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

            $remember = !empty($input['remember']) && ($input['remember'] === '1' || $input['remember'] === 'true' || $input['remember'] === 'on');
            $user = $this->authService->login($loginIdentifier, $password, $remember);

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
