<?php
namespace App\Services;

use App\Repositories\EmailLogRepository;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Refund;
use Throwable;

/**
 * Service for Safe, Idempotency-Aware Retrying of Failed Email Notifications
 */
class EmailRetryService
{
    private EmailLogRepository $repository;
    private EmailTransportService $transport;
    private EmailLogService $logService;
    private EmailTemplateService $templateService;

    public function __construct(
        ?EmailLogRepository $repository = null,
        ?EmailTransportService $transport = null,
        ?EmailLogService $logService = null,
        ?EmailTemplateService $templateService = null
    ) {
        $this->repository = $repository ?? new EmailLogRepository();
        $this->transport = $transport ?? new EmailTransportService();
        $this->logService = $logService ?? new EmailLogService();
        $this->templateService = $templateService ?? new EmailTemplateService();
    }

    /**
     * Retry sending an email that previously failed
     *
     * @param int $logId ID from email_logs table
     * @return array ['success' => bool, 'message' => string, 'duplicate' => bool, 'error' => ?string]
     */
    public function retry(int $logId): array
    {
        $log = $this->repository->find($logId);
        if (!$log) {
            return [
                'success'   => false,
                'duplicate' => false,
                'message'   => "Email log #{$logId} was not found.",
                'error'     => 'Log not found.'
            ];
        }

        // Prevent duplicate delivery: if already successfully sent, disallow retries
        if ($log['status'] === 'sent') {
            return [
                'success'   => false,
                'duplicate' => true,
                'message'   => "Email #{$logId} has already been sent successfully. Duplicate delivery prevented.",
                'error'     => 'Email already sent.'
            ];
        }

        $attempts = (int)($log['attempt_count'] ?? 0);
        $maxAttempts = (int)($log['max_attempts'] ?? 5);
        if ($attempts >= $maxAttempts) {
            return [
                'success'   => false,
                'duplicate' => false,
                'message'   => "Maximum delivery attempts ({$maxAttempts}) reached for this notification.",
                'error'     => 'Max attempts reached.'
            ];
        }

        $toEmail = $log['recipient_email'] ?? ($log['to_email'] ?? '');
        $toName = $log['recipient_name'] ?? ($log['to_name'] ?? null);
        $subject = $log['subject'] ?? '';
        $htmlBody = $log['html_body'] ?? '';
        $textBody = $log['text_body'] ?? null;

        $cc = [];
        if (!empty($log['cc'])) {
            $cc = is_array($log['cc']) ? $log['cc'] : (json_decode($log['cc'], true) ?: []);
        }
        $bcc = [];
        if (!empty($log['bcc'])) {
            $bcc = is_array($log['bcc']) ? $log['bcc'] : (json_decode($log['bcc'], true) ?: []);
        }

        // Case 1: Stored HTML body exists: direct re-transmission
        if (!empty($htmlBody)) {
            try {
                $msgId = $this->transport->send($toEmail, $toName, $subject, $htmlBody, $textBody, $cc, $bcc);
                $this->logService->recordRetryOutcome($logId, 'sent', null, $msgId);

                return [
                    'success'    => true,
                    'duplicate'  => false,
                    'message'    => "Email #{$logId} sent successfully via retry.",
                    'message_id' => $msgId,
                    'error'      => null
                ];
            } catch (Throwable $e) {
                $err = $e->getMessage();
                $this->logService->recordRetryOutcome($logId, 'failed', $err, null);

                return [
                    'success'   => false,
                    'duplicate' => false,
                    'message'   => "Retry attempt failed: " . $err,
                    'error'     => $err
                ];
            }
        }

        // Case 2: Legacy row without stored body: reconstruct using entity reference
        $emailType = $log['email_type'] ?? '';
        $refId = !empty($log['reference_id']) ? (int)$log['reference_id'] : null;

        try {
            $reconstructedHtml = null;
            $reconstructedSubject = $subject;

            switch ($emailType) {
                case 'customer_welcome':
                    if ($refId) {
                        $cust = (new Customer())->find($refId);
                        if ($cust) {
                            $reconstructedSubject = "Welcome to " . CompanySettingsService::getCompanyName() . " — Your Account is Ready";
                            $reconstructedHtml = $this->templateService->render('customer/welcome', ['customer' => $cust]);
                        }
                    }
                    break;

                case 'employee_welcome':
                    if ($refId) {
                        $emp = (new Employee())->find($refId);
                        if ($emp) {
                            $reconstructedSubject = "Welcome to the Team — " . CompanySettingsService::getCompanyName();
                            $reconstructedHtml = $this->templateService->render('employee/welcome', ['employee' => $emp]);
                        }
                    }
                    break;

                case 'booking_created':
                    if ($refId) {
                        $b = (new Booking())->find($refId);
                        if ($b) {
                            $reconstructedSubject = "Booking Received: {$b['booking_code']} — " . CompanySettingsService::getCompanyName();
                            $reconstructedHtml = $this->templateService->render('booking/created', ['booking' => $b]);
                        }
                    }
                    break;

                case 'booking_confirmed':
                    if ($refId) {
                        $b = (new Booking())->find($refId);
                        if ($b) {
                            $reconstructedSubject = "Booking Confirmed: {$b['booking_code']} — " . CompanySettingsService::getCompanyName();
                            $reconstructedHtml = $this->templateService->render('booking/confirmed', ['booking' => $b]);
                        }
                    }
                    break;

                case 'booking_cancelled':
                    if ($refId) {
                        $b = (new Booking())->find($refId);
                        if ($b) {
                            $reconstructedSubject = "Booking Cancellation: {$b['booking_code']} — " . CompanySettingsService::getCompanyName();
                            $reconstructedHtml = $this->templateService->render('booking/cancelled', ['booking' => $b, 'reason' => 'Administrative retry']);
                        }
                    }
                    break;

                case 'payment_successful':
                    if ($refId) {
                        $p = (new Payment())->find($refId);
                        if ($p) {
                            $reconstructedSubject = "Payment Receipt: {$p['payment_code']} — " . CompanySettingsService::getCompanyName();
                            $reconstructedHtml = $this->templateService->render('payment/successful', ['payment' => $p]);
                        }
                    }
                    break;

                case 'test':
                    $reconstructedSubject = "SMTP Diagnostic Test — " . CompanySettingsService::getCompanyName();
                    $reconstructedHtml = $this->templateService->render('test', ['recipientEmail' => $toEmail]);
                    break;
            }

            if (empty($reconstructedHtml)) {
                return [
                    'success'   => false,
                    'duplicate' => false,
                    'message'   => "Could not reconstruct payload for email type '{$emailType}'. Related record may have been deleted.",
                    'error'     => 'Payload missing and record not found.'
                ];
            }

            $msgId = $this->transport->send($toEmail, $toName, $reconstructedSubject, $reconstructedHtml, null, $cc, $bcc);
            $this->logService->recordRetryOutcome($logId, 'sent', null, $msgId);

            return [
                'success'    => true,
                'duplicate'  => false,
                'message'    => "Email #{$logId} reconstructed and sent successfully.",
                'message_id' => $msgId,
                'error'      => null
            ];

        } catch (Throwable $e) {
            $err = $e->getMessage();
            $this->logService->recordRetryOutcome($logId, 'failed', $err, null);

            return [
                'success'   => false,
                'duplicate' => false,
                'message'   => "Retry attempt failed: " . $err,
                'error'     => $err
            ];
        }
    }
}

