<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Services\EmailService;
use App\Models\EmailLog;
use Throwable;

class EmailTestController extends Controller
{
    private EmailService $emailService;
    private EmailLog $emailLog;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->emailLog = new EmailLog();
    }

    /**
     * Admin view for testing SMTP connectivity, background worker telemetry and outbox queue
     */
    public function index(Request $request): Response
    {
        $status = $request->get('status');
        if (!in_array($status, ['pending', 'processing', 'sent', 'failed'], true)) {
            $status = null;
        }

        $logs = $this->emailLog->getFilteredLogs($status, 50);
        $workerHealth = $this->emailLog->getWorkerHealth();
        $queueCounts = $this->emailLog->getQueueCounts();

        // Sanitize and mask sender email for display
        $fromEmail = Config::get('mail.from.address', '');
        $maskedEmail = $this->maskEmail($fromEmail);

        return $this->render('office/email/test', [
            'title'        => 'SMTP Email System Diagnostics & Worker Telemetry',
            'smtpHost'     => Config::get('mail.host', 'smtp.gmail.com'),
            'smtpPort'     => Config::get('mail.port', 465),
            'encryption'   => Config::get('mail.encryption', 'ssl'),
            'fromName'     => Config::get('mail.from.name', \App\Services\CompanySettingsService::getCompanyName()),
            'senderEmail'  => $maskedEmail,
            'isConfigured' => !empty(Config::get('mail.username')) && !empty(Config::get('mail.password')),
            'logs'         => $logs,
            'statusFilter' => $status,
            'workerHealth' => $workerHealth,
            'queueCounts'  => $queueCounts,
        ], 'layouts/office');
    }

    /**
     * AJAX endpoint to inspect sanitized email log details
     */
    public function details(Request $request, int $id): Response
    {
        $log = $this->emailLog->find($id);
        if (!$log) {
            return $this->json(['success' => false, 'message' => 'Log not found.'], 404);
        }

        // Sanitize body to avoid leaking any passwords or auth tokens
        $htmlBody = $log['html_body'] ?? '';
        $htmlBody = preg_replace('/(password|token|secret)[=\s:]+[^\s&<"\'\\]+/i', '$1=[REDACTED]', $htmlBody);

        return $this->json([
            'success' => true,
            'log'     => [
                'id'              => (int)$log['id'],
                'recipient_email' => $log['recipient_email'] ?? ($log['to_email'] ?? ''),
                'recipient_name'  => $log['recipient_name'] ?? ($log['to_name'] ?? ''),
                'email_type'      => $log['email_type'],
                'subject'         => $log['subject'],
                'status'          => $log['status'],
                'attempt_count'   => (int)($log['attempt_count'] ?? 0),
                'max_attempts'    => (int)($log['max_attempts'] ?? 5),
                'next_attempt_at' => $log['next_attempt_at'],
                'locked_at'       => $log['locked_at'],
                'locked_by'       => $log['locked_by'],
                'sent_at'         => $log['sent_at'],
                'created_at'      => $log['created_at'],
                'error_message'   => $log['error_message'],
                'message_id'      => $log['message_id'],
                'html_body'       => $htmlBody,
            ]
        ]);
    }

    /**
     * Handle test email transmission request
     */
    public function sendTest(Request $request): Response
    {
        try {
            $input = $this->validate($request->all(), [
                'recipient_email' => 'required|email|max:150',
            ]);

            $recipientEmail = trim($input['recipient_email']);
            $result = $this->emailService->sendTestEmail($recipientEmail);

            if (!empty($result['success'])) {
                Session::setFlash('success', "Test message successfully dispatched to '{$recipientEmail}'. Check inbox and spam folders.");
            } else {
                Session::setFlash('danger', "Failed to dispatch test message: " . ($result['error'] ?? 'Unknown SMTP error'));
            }
        } catch (Throwable $e) {
            Session::setFlash('danger', "Error: " . $e->getMessage());
        }

        $referer = $request->header('referer');
        if (!empty($referer) && str_contains($referer, 'notification-settings')) {
            return $this->redirect(url('/office/notification-settings'));
        }
        return $this->redirect(url('/office/email/test'));
    }

    /**
     * Handle manual retry of a failed outgoing notification
     */
    public function retry(Request $request, int $id): Response
    {
        try {
            $result = $this->emailService->retryFailedEmail($id);
            if (!empty($result['success'])) {
                Session::setFlash('success', "Email #{$id} was successfully dispatched upon retry.");
            } elseif (!empty($result['duplicate'])) {
                Session::setFlash('info', $result['message']);
            } else {
                Session::setFlash('danger', "Email retry failed: " . ($result['error'] ?? ($result['message'] ?? 'SMTP Error')));
            }
        } catch (Throwable $e) {
            Session::setFlash('danger', "Error during retry: " . $e->getMessage());
        }

        $referer = $request->header('referer');
        if (!empty($referer) && str_contains($referer, 'notification-settings')) {
            return $this->redirect(url('/office/notification-settings'));
        }
        return $this->redirect(url('/office/email/test'));
    }

    /**
     * Helper to mask email for security
     */
    private function maskEmail(string $email): string
    {
        if (empty($email) || !str_contains($email, '@')) {
            return 'Not Configured';
        }

        $parts = explode('@', $email, 2);
        $name = $parts[0];
        $domain = $parts[1];

        $len = strlen($name);
        if ($len <= 2) {
            $maskedName = substr($name, 0, 1) . '*';
        } else {
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(1, $len - 3)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }
}
