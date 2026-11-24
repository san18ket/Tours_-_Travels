<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\NotificationService;
use Throwable;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Office Notifications Hub (Search, Filter, Pagination, Mark Read)
     */
    public function index(Request $request): Response
    {
        try {
            $userId = (int)Session::getUserId();
            $search = trim((string)$request->get('search', ''));
            $status = trim((string)$request->get('status', ''));
            $page = max(1, (int)$request->get('page', 1));
            $perPage = in_array((int)$request->get('per_page'), [10, 25, 50], true) ? (int)$request->get('per_page') : 10;

            $kpis = $this->notificationService->getKpiStats($userId);
            $totalRecords = $this->notificationService->countFiltered($userId, null, $search, $status);
            $notifications = $this->notificationService->getPaginatedNotifications($userId, null, $search, $status, $page, $perPage);
            $totalPages = max(1, (int)ceil($totalRecords / $perPage));
            $unreadNotifCount = $kpis['total_unread'] ?? 0;

            return $this->render('office/notifications/index', [
                'title'            => 'Notifications & Operation Alerts',
                'notifications'    => $notifications,
                'totalRecords'     => $totalRecords,
                'totalPages'       => $totalPages,
                'page'             => $page,
                'perPage'          => $perPage,
                'search'           => $search,
                'status'           => $status,
                'kpis'             => $kpis,
                'unreadNotifCount' => $unreadNotifCount,
            ], 'layouts/office');
        } catch (Throwable $e) {
            Session::setFlash('danger', 'Unable to load notifications: ' . $e->getMessage());
            return $this->redirect(url('/office/dashboard'));
        }
    }

    /**
     * Mark single notification as read
     */
    public function markRead(Request $request, array $params = []): Response
    {
        try {
            $id = (int)($params['id'] ?? $request->input('id', 0));
            $userId = (int)Session::getUserId();

            if ($id <= 0) {
                return $this->json(['success' => false, 'error' => 'Invalid notification ID'], 400);
            }

            $this->notificationService->markAsRead($id, $userId);
            $unread = $this->notificationService->getUnreadCount($userId);

            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json([
                    'success'      => true,
                    'unread_count' => $unread,
                    'id'           => $id,
                ]);
            }

            return $this->redirect(url('/office/notifications'));
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/notifications'));
        }
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllRead(Request $request): Response
    {
        try {
            $userId = (int)Session::getUserId();
            $this->notificationService->markAllAsRead($userId);

            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json([
                    'success'      => true,
                    'unread_count' => 0,
                    'message'      => 'All notifications marked as read',
                ]);
            }

            Session::setFlash('success', 'All notifications marked as read.');
            return $this->redirect(url('/office/notifications'));
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/notifications'));
        }
    }

    /**
     * Fetch live unread count
     */
    public function unreadCount(Request $request): Response
    {
        $userId = (int)Session::getUserId();
        $unread = $userId > 0 ? $this->notificationService->getUnreadCount($userId) : 0;

        return $this->json([
            'success'      => true,
            'unread_count' => $unread,
        ]);
    }

    /**
     * Office Notification Settings Page
     */
    public function settings(Request $request): Response
    {
        try {
            $userId = (int)Session::getUserId();
            $preferences = $this->notificationService->getUserPreferences($userId);
            $devices = []; // Push/device list if applicable

            return $this->render('office/settings/notifications', [
                'title'       => 'Notification Settings',
                'preferences' => $preferences,
                'devices'     => $devices,
            ], 'layouts/office');
        } catch (Throwable $e) {
            Session::setFlash('danger', 'Unable to load notification settings: ' . $e->getMessage());
            return $this->redirect(url('/office/dashboard'));
        }
    }

    /**
     * Save Notification Preferences (Account-Scoped & Secure)
     */
    public function saveSettings(Request $request): Response
    {
        try {
            // Backward compatibility: If an email test is triggered via this route
            if ($request->input('action') === 'send_test' || $request->has('recipient_email')) {
                return (new EmailTestController())->sendTest($request);
            }

            $userId = (int)Session::getUserId();
            if ($userId <= 0) {
                if ($request->isAjax() || $request->wantsJson()) {
                    return $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
                }
                return $this->redirect(url('/login'));
            }

            // Client-provided user_id is ignored to guarantee account scoping
            $input = $request->all();
            $success = $this->notificationService->saveUserPreferences($userId, $input);

            if (!$success) {
                throw new \RuntimeException('Failed to persist notification settings to database.');
            }

            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Notification preferences updated successfully.',
                ]);
            }

            Session::setFlash('success', 'Notification preferences updated successfully.');
            return $this->redirect(url('/office/notification-settings'));
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->wantsJson()) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', 'Error saving settings: ' . $e->getMessage());
            return $this->redirect(url('/office/notification-settings'));
        }
    }
}

