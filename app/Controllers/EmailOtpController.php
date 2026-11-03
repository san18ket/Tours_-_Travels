<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Logger;
use App\Services\EmailOtpService;
use Throwable;

class EmailOtpController extends Controller
{
    private EmailOtpService $otpService;

    public function __construct()
    {
        $this->otpService = new EmailOtpService();
    }

    /**
     * Dispatch initial OTP to email
     */
    public function sendOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $flow = trim((string)$request->input('flow', 'customer'));
            $recipientName = trim((string)$request->input('name', ''));
            $clientIp = $request->getClientIp();

            if (empty($email)) {
                return Response::json([
                    'success'  => false,
                    'message'  => 'Please enter an email address to send the verification code.',
                    'cooldown' => 0
                ], 422);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return Response::json([
                    'success'  => false,
                    'message'  => 'Please enter a valid email address.',
                    'cooldown' => 0
                ], 422);
            }

            $result = $this->otpService->sendOtp($email, $flow, $recipientName ?: null, $clientIp);

            $statusCode = $result['success'] ? 200 : 422;
            return Response::json([
                'success'  => $result['success'],
                'message'  => $result['message'],
                'cooldown' => $result['cooldown'] ?? 0,
            ], $statusCode);
        } catch (Throwable $e) {
            Logger::error("EmailOtpController::sendOtp exception: " . $e->getMessage());
            return Response::json([
                'success'  => false,
                'message'  => 'An error occurred while dispatching the verification code. Please try again.',
                'cooldown' => 0
            ], 500);
        }
    }

    /**
     * Resend a fresh OTP to the email
     */
    public function resendOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $flow = trim((string)$request->input('flow', 'customer'));
            $recipientName = trim((string)$request->input('name', ''));
            $clientIp = $request->getClientIp();

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return Response::json([
                    'success'  => false,
                    'message'  => 'A valid email address is required to resend the code.',
                    'cooldown' => 0
                ], 422);
            }

            $result = $this->otpService->sendOtp($email, $flow, $recipientName ?: null, $clientIp);

            $statusCode = $result['success'] ? 200 : 422;
            return Response::json([
                'success'  => $result['success'],
                'message'  => $result['message'],
                'cooldown' => $result['cooldown'] ?? 0,
            ], $statusCode);
        } catch (Throwable $e) {
            Logger::error("EmailOtpController::resendOtp exception: " . $e->getMessage());
            return Response::json([
                'success'  => false,
                'message'  => 'Failed to resend verification code. Please try again.',
                'cooldown' => 0
            ], 500);
        }
    }

    /**
     * Verify 4-digit OTP code submitted by user
     */
    public function verifyOtp(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $flow = trim((string)$request->input('flow', 'customer'));
            $otp = trim((string)$request->input('otp', ''));

            if (empty($email)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Email address is required.'
                ], 422);
            }

            if (!preg_match('/^\d{4}$/', $otp)) {
                return Response::json([
                    'success' => false,
                    'message' => 'Please enter all 4 digits of the verification code.'
                ], 422);
            }

            $result = $this->otpService->verifyOtp($email, $flow, $otp);

            $statusCode = $result['success'] ? 200 : 422;
            return Response::json([
                'success' => $result['success'],
                'message' => $result['message'],
                'token'   => $result['token'] ?? null
            ], $statusCode);
        } catch (Throwable $e) {
            Logger::error("EmailOtpController::verifyOtp exception: " . $e->getMessage());
            return Response::json([
                'success' => false,
                'message' => 'Verification failed due to a server error. Please try again.'
            ], 500);
        }
    }

    /**
     * Invalidate active verification when email input changes
     */
    public function invalidate(Request $request): Response
    {
        try {
            $email = trim((string)$request->input('email', ''));
            $flow = trim((string)$request->input('flow', 'customer'));

            if (!empty($email)) {
                $this->otpService->invalidateVerification($email, $flow);
            }

            return Response::json([
                'success' => true,
                'message' => 'Verification reset.'
            ]);
        } catch (Throwable $e) {
            return Response::json([
                'success' => true,
                'message' => 'Reset completed.'
            ]);
        }
    }
}

