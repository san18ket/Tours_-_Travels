<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Logger;
use App\Services\AccountRecoveryService;
use Throwable;

class AccountRecoveryController extends Controller
{
    private AccountRecoveryService $recoveryService;

    public function __construct()
    {
        $this->recoveryService = new AccountRecoveryService();
    }

    /**
     * Account Recovery Hub: Option A (Forgot Username) & Option B (Forgot Password)
     */
    public function showRecoveryHub(Request $request): Response
    {
        if (Session::isLoggedIn()) {
            return Response::redirect(url('/office/dashboard'));
        }

        return $this->render('auth/forgot_account', [
            'title' => 'Recover Your Account - ' . company_display_name()
        ], 'layouts/auth');
    }

    /**
     * Forgot Username view
     */
    public function showForgotUsername(Request $request): Response
    {
        if (Session::isLoggedIn()) {
            return Response::redirect(url('/office/dashboard'));
        }

        return $this->render('auth/forgot_username', [
            'title' => 'Recover Username - ' . company_display_name()
        ], 'layouts/auth');
    }

    /**
     * Send OTP for Username Recovery
     */
    public function sendUsernameOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $clientIp = $request->getClientIp();

            $result = $this->recoveryService->sendRecoveryOtp($email, AccountRecoveryService::FLOW_USERNAME, $clientIp);

            $status = $result['success'] ? 200 : 422;
            return Response::json([
                'success'  => $result['success'],
                'message'  => $result['message'],
                'cooldown' => $result['cooldown'] ?? 0,
            ], $status);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::sendUsernameOtp exception: " . $e->getMessage());
            return Response::json([
                'success'  => false,
                'message'  => 'An error occurred while dispatching the verification code. Please try again.',
                'cooldown' => 0
            ], 500);
        }
    }

    /**
     * Verify OTP for Username Recovery
     */
    public function verifyUsernameOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $otp = trim((string)$request->input('otp', ''));
            $clientIp = $request->getClientIp();

            $result = $this->recoveryService->verifyRecoveryOtp($email, AccountRecoveryService::FLOW_USERNAME, $otp, $clientIp);

            $status = $result['success'] ? 200 : 422;
            return Response::json([
                'success' => $result['success'],
                'message' => $result['message'],
                'token'   => $result['token'] ?? null
            ], $status);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::verifyUsernameOtp exception: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Verification failed due to a server error. Please try again.',
                'token'   => null
            ], 500);
        }
    }

    /**
     * Reveal Recovered Username using verified authorization token
     */
    public function recoverUsername(Request $request): Response
    {
        try {
            $token = trim((string)($request->input('token') ?? $request->input('recovery_token') ?? ''));

            if (empty($token)) {
                return Response::json([
                    'success'  => false,
                    'username' => null,
                    'message'  => 'Missing recovery authorization token.'
                ], 422);
            }

            $result = $this->recoveryService->recoverUsername($token);

            $status = $result['success'] ? 200 : 422;
            return Response::json($result, $status);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::recoverUsername exception: " . $e->getMessage());
            return Response::json([
                'success'  => false,
                'username' => null,
                'message'  => 'Failed to recover username. Please try again.'
            ], 500);
        }
    }

    /**
     * Forgot Password view
     */
    public function showForgotPassword(Request $request): Response
    {
        if (Session::isLoggedIn()) {
            return Response::redirect(url('/office/dashboard'));
        }

        return $this->render('auth/forgot_password', [
            'title' => 'Reset Password - ' . company_display_name()
        ], 'layouts/auth');
    }

    /**
     * Send OTP for Password Recovery
     */
    public function sendPasswordOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $clientIp = $request->getClientIp();

            $result = $this->recoveryService->sendRecoveryOtp($email, AccountRecoveryService::FLOW_PASSWORD, $clientIp);

            $status = $result['success'] ? 200 : 422;
            return Response::json([
                'success'  => $result['success'],
                'message'  => $result['message'],
                'cooldown' => $result['cooldown'] ?? 0,
            ], $status);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::sendPasswordOtp exception: " . $e->getMessage());
            return Response::json([
                'success'  => false,
                'message'  => 'An error occurred while dispatching the verification code. Please try again.',
                'cooldown' => 0
            ], 500);
        }
    }

    /**
     * Verify OTP for Password Recovery
     */
    public function verifyPasswordOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $otp = trim((string)$request->input('otp', ''));
            $clientIp = $request->getClientIp();

            $result = $this->recoveryService->verifyRecoveryOtp($email, AccountRecoveryService::FLOW_PASSWORD, $otp, $clientIp);

            $status = $result['success'] ? 200 : 422;
            return Response::json([
                'success' => $result['success'],
                'message' => $result['message'],
                'token'   => $result['token'] ?? null
            ], $status);
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::verifyPasswordOtp exception: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Verification failed due to a server error. Please try again.',
                'token'   => null
            ], 500);
        }
    }

    /**
     * Create New Password View
     */
    public function showResetPassword(Request $request): Response
    {
        if (Session::isLoggedIn()) {
            return Response::redirect(url('/office/dashboard'));
        }

        $token = trim((string)($request->input('token') ?? ''));

        // If no token in URL, check session
        if (empty($token)) {
            $sessAuth = Session::get('recovery_auth_' . AccountRecoveryService::FLOW_PASSWORD);
            $token = $sessAuth['token'] ?? '';
        }

        if (empty($token)) {
            Session::setFlash('danger', 'Password reset session expired or invalid. Please request a new verification code.');
            return Response::redirect(url('/forgot-password'));
        }

        $validated = $this->recoveryService->validateRecoveryAuthorization($token, AccountRecoveryService::FLOW_PASSWORD);
        if (!$validated) {
            Session::setFlash('danger', 'Password reset session expired or invalid. Please request a new verification code.');
            return Response::redirect(url('/forgot-password'));
        }

        return $this->render('auth/reset_password', [
            'title' => 'Create New Password - ' . company_display_name(),
            'token' => $token,
            'user'  => $validated['user']
        ], 'layouts/auth');
    }

    /**
     * Process New Password Submission
     */
    public function resetPassword(Request $request): Response
    {
        try {
            $token = trim((string)($request->input('token') ?? $request->input('recovery_token') ?? ''));
            $newPassword = (string)$request->input('password', '');
            $confirmPassword = (string)($request->input('password_confirmation') ?? $request->input('confirm_password') ?? '');

            if (empty($token)) {
                $sessAuth = Session::get('recovery_auth_' . AccountRecoveryService::FLOW_PASSWORD);
                $token = $sessAuth['token'] ?? '';
            }

            $result = $this->recoveryService->resetPassword($token, $newPassword, $confirmPassword);

            if ($request->isAjax()) {
                $status = $result['success'] ? 200 : 422;
                return Response::json($result, $status);
            }

            if ($result['success']) {
                Session::setFlash('success', 'Your password has been updated. You can now log in using your new password.');
                return Response::redirect(url('/login'));
            }

            Session::setFlash('danger', $result['message']);
            return Response::redirect(url('/forgot-password/reset?token=' . urlencode($token)));
        } catch (Throwable $e) {
            Logger::error("AccountRecoveryController::resetPassword exception: " . $e->getMessage());

            if ($request->isAjax()) {
                return Response::json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while resetting your password.'
                ], 500);
            }

            Session::setFlash('danger', 'An unexpected error occurred. Please try again.');
            return Response::redirect(url('/forgot-password'));
        }
    }
}

