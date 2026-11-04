<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\EmailService;
use App\Services\EmailSettingsService;
use App\Services\EmailTemplateService;
use App\Services\EmailRetryService;
use App\Repositories\EmailLogRepository;
use Throwable;

/**
 * Professional Email Subsystem Controller
 * Handles Email Management Dashboard, Audit History, Controlled Retries,
 * SMTP Diagnostics, Settings, and Mobile-First Template Previews.
 */
class EmailController extends Controller
{
    private EmailService $emailService;
    private EmailLogRepository $repository;
    private EmailTemplateService $templateService;
    private EmailRetryService $retryService;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->repository = new EmailLogRepository();
        $this->templateService = new EmailTemplateService();
        $this->retryService = new EmailRetryService($this->repository);
    }

    /**
     * Dashboard Overview (Executive stats, SMTP status, recent activity)
     */
    public function index(Request $request): Response
    {
        $stats = $this->repository->getDashboardStats();
        $recentLogs = $this->repository->getFilteredLogs([], 1, 10)['items'];
        $smtpConfig = EmailSettingsService::getDisplaySettings();
        $activeTab = 'overview';

        return $this->render('office/emails/index', [
            'title'      => 'Email Management & Delivery Center',
            'stats'      => $stats,
            'recentLogs' => $recentLogs,
            'smtpConfig' => $smtpConfig,
            'activeTab'  => $activeTab,
        ], 'layouts/office');
    }

    /**
     * Email History (Search, filter by status & type, pagination, retry)
     */
    public function history(Request $request): Response
    {
        $filters = [
            'status'    => $request->get('status', 'all'),
            'type'      => $request->get('type', 'all'),
            'search'    => $request->get('search', ''),
            'from_date' => $request->get('from_date', ''),
            'to_date'   => $request->get('to_date', ''),
        ];

        $page = (int)$request->get('page', 1);
        $perPage = 20;

        $logData = $this->repository->getFilteredLogs($filters, $page, $perPage);
        $types = $this->repository->getDistinctTypes();
        $stats = $this->repository->getDashboardStats();

        return $this->render('office/emails/history', [
            'title'     => 'Email Audit History & Outbox',
            'logs'      => $logData['items'],
            'total'     => $logData['total'],
            'page'      => $logData['page'],
            'perPage'   => $logData['per_page'],
            'totalPages'=> $logData['total_pages'],
            'filters'   => $filters,
            'types'     => $types,
            'stats'     => $stats,
            'activeTab' => 'history',
        ], 'layouts/office');
    }

    /**
     * Inspect Sanitized Details for a Single Email Log
     */
    public function details(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? $request->get('id', 0));
        $log = $this->repository->find($id);

        if (!$log) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Email log not found.'], 404);
            }
            Session::setFlash('danger', 'Email record not found.');
            return $this->redirect(url('/office/emails/history'));
        }

        // Sanitize body to prevent leaking auth tokens or secrets
        $htmlBody = $log['html_body'] ?? '';
        $htmlBody = preg_replace('/(password|token|secret)[=\s:]+[^\s&<"\'\\]+/i', '$1=[REDACTED]', $htmlBody);

        if ($request->isAjax()) {
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
                    'reference_type'  => $log['reference_type'],
                    'reference_id'    => $log['reference_id'],
                    'sent_at'         => $log['sent_at'],
                    'last_attempt_at' => $log['last_attempt_at'],
                    'created_at'      => $log['created_at'],
                    'error_message'   => $log['error_message'],
                    'message_id'      => $log['message_id'],
                    'html_body'       => $htmlBody,
                ]
            ]);
        }

        return $this->render('office/emails/details', [
            'title'     => 'Email Record #' . $id,
            'log'       => $log,
            'htmlBody'  => $htmlBody,
            'activeTab' => 'history',
        ], 'layouts/office');
    }

    /**
     * Manual Safe Retry of a Failed Outgoing Notification
     */
    public function retry(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? $request->post('id', 0));

        try {
            $result = $this->emailService->retryFailedEmail($id);

            if (!empty($result['success'])) {
                $msg = "Email #{$id} was successfully dispatched upon retry.";
                if ($request->isAjax()) {
                    return $this->json(['success' => true, 'message' => $msg]);
                }
                Session::setFlash('success', $msg);
            } elseif (!empty($result['duplicate'])) {
                $msg = $result['message'];
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'duplicate' => true, 'message' => $msg]);
                }
                Session::setFlash('info', $msg);
            } else {
                $err = $result['error'] ?? ($result['message'] ?? 'SMTP delivery failed on retry.');
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('danger', "Retry failed: {$err}");
            }
        } catch (Throwable $e) {
            $err = $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('danger', "Error during retry: {$err}");
        }

        $referer = $request->header('referer');
        if (!empty($referer) && !str_contains($referer, 'retry')) {
            return $this->redirect($referer);
        }
        return $this->redirect(url('/office/emails/history'));
    }

    /**
     * SMTP Settings & Deliverability Guidelines View
     */
    public function settings(Request $request): Response
    {
        $smtpConfig = EmailSettingsService::getDisplaySettings();
        $guidance = EmailSettingsService::getDeliverabilityGuidance();

        return $this->render('office/emails/settings', [
            'title'      => 'SMTP Settings & Domain Deliverability',
            'smtpConfig' => $smtpConfig,
            'guidance'   => $guidance,
            'activeTab'  => 'settings',
        ], 'layouts/office');
    }

    /**
     * Diagnostic Socket Connection Test (Handshake without sending an email)
     */
    public function testSmtp(Request $request): Response
    {
        $result = EmailSettingsService::testConnection();
        return $this->json($result);
    }

    /**
     * Send Real Test Email Action
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
                $msg = "Diagnostic test message successfully dispatched to '{$recipientEmail}'. Please verify your inbox.";
                if ($request->isAjax()) {
                    return $this->json(['success' => true, 'message' => $msg]);
                }
                Session::setFlash('success', $msg);
            } else {
                $err = $result['error'] ?? 'SMTP delivery failed.';
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => $err], 422);
                }
                Session::setFlash('danger', "Failed to dispatch test message: {$err}");
            }
        } catch (Throwable $e) {
            $err = $e->getMessage();
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err], 500);
            }
            Session::setFlash('danger', "Error: {$err}");
        }

        $referer = $request->header('referer');
        if (!empty($referer)) {
            return $this->redirect($referer);
        }
        return $this->redirect(url('/office/emails/settings'));
    }

    /**
     * Template Previewer Page (Dropdown selector & mobile/desktop toggle)
     */
    public function preview(Request $request): Response
    {
        $templates = EmailTemplateService::getRegisteredTemplates();
        $selected = $request->get('template', 'booking/confirmed');

        if (!array_key_exists($selected, $templates)) {
            $selected = 'booking/confirmed';
        }

        $themeColors = \App\Services\CompanySettingsService::getThemeColors();

        return $this->render('office/emails/preview', [
            'title'            => 'Email Template Design & Brand Previewer',
            'templates'        => $templates,
            'selectedTemplate' => $selected,
            'themeColors'      => $themeColors,
            'activeTab'        => 'preview',
        ], 'layouts/office');
    }

    /**
     * Render raw HTML of a template with sample data for iframe embedding
     */
    public function previewRender(Request $request): Response
    {
        $template = $request->get('template', 'booking/confirmed');
        $templates = EmailTemplateService::getRegisteredTemplates();

        if (!array_key_exists($template, $templates)) {
            $template = 'booking/confirmed';
        }

        try {
            $sampleData = $this->templateService->getSampleDataForTemplate($template);
            $html = $this->templateService->render($template, $sampleData);

            $res = new Response($html);
            $res->setHeader('Content-Type', 'text/html; charset=UTF-8');
            return $res;
        } catch (Throwable $e) {
            return new Response('<div style="padding:20px; color:#DC2626; font-family:sans-serif;"><h3>Template Render Error</h3><p>' . htmlspecialchars($e->getMessage()) . '</p></div>', 500);
        }
    }
}

