<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Logger;
use App\Models\PushSubscription;
use App\Services\WebPushService;

class PushSubscriptionController extends Controller
{
    private PushSubscription $subscriptionModel;
    private WebPushService $pushService;

    public function __construct()
    {
        $this->subscriptionModel = new PushSubscription();
        $this->pushService = new WebPushService();
    }

    /**
     * Return public VAPID key for browser PushManager subscription
     */
    public function getVapidPublicKey(Request $request): Response
    {
        $publicKey = $this->pushService->getVapidPublicKey();
        if (empty($publicKey)) {
            return $this->json([
                'success' => false,
                'error'   => 'VAPID public key is not configured.'
            ], 500);
        }

        return $this->json([
            'success'    => true,
            'public_key' => $publicKey
        ]);
    }

    /**
     * Register or update push subscription for current authenticated user
     */
    public function register(Request $request): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json([
                'success' => false,
                'error'   => 'Authentication required.'
            ], 401);
        }

        $input = $request->isJson() ? $request->getJson() : $request->all();

        $endpoint = trim($input['endpoint'] ?? '');
        $p256dh = trim($input['keys']['p256dh'] ?? $input['p256dh'] ?? '');
        $auth = trim($input['keys']['auth'] ?? $input['auth'] ?? '');
        $contentEncoding = trim($input['content_encoding'] ?? 'aes128gcm');
        $deviceName = trim($input['device_name'] ?? '');

        // Validation
        if (empty($endpoint) || !filter_var($endpoint, FILTER_VALIDATE_URL)) {
            return $this->json([
                'success' => false,
                'error'   => 'A valid push service endpoint URL is required.'
            ], 422);
        }

        if (empty($p256dh) || empty($auth)) {
            return $this->json([
                'success' => false,
                'error'   => 'Missing cryptographic subscription keys (p256dh, auth).'
            ], 422);
        }

        if (empty($deviceName)) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
            $deviceName = $this->parseDeviceName($userAgent);
        }

        try {
            $subId = $this->subscriptionModel->saveSubscription(
                (int)$userId,
                $endpoint,
                $p256dh,
                $auth,
                $contentEncoding,
                $deviceName
            );

            Logger::info("User #{$userId} registered push subscription #{$subId} on '{$deviceName}'");

            return $this->json([
                'success'         => true,
                'subscription_id' => $subId,
                'message'         => 'Push subscription registered successfully.'
            ]);
        } catch (\Throwable $e) {
            Logger::error("Failed to register push subscription for user #{$userId}: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'error'   => 'Unable to register push subscription.'
            ], 500);
        }
    }

    /**
     * Unsubscribe / revoke push subscription for current device
     */
    public function unsubscribe(Request $request): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $input = $request->isJson() ? $request->getJson() : $request->all();
        $endpoint = trim($input['endpoint'] ?? '');
        $subscriptionId = !empty($input['subscription_id']) ? (int)$input['subscription_id'] : null;

        if (!empty($endpoint)) {
            $this->subscriptionModel->revokeByEndpoint($endpoint);
        } elseif ($subscriptionId) {
            $this->subscriptionModel->revoke($subscriptionId, (int)$userId);
        } else {
            // Revoke all for current user if neither specified
            $subscriptions = $this->subscriptionModel->getActiveForUser((int)$userId);
            foreach ($subscriptions as $s) {
                $this->subscriptionModel->revoke((int)$s['id'], (int)$userId);
            }
        }

        Logger::info("User #{$userId} unsubscribed from push notifications.");

        return $this->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from notifications.'
        ]);
    }

    /**
     * Get current user's push status and active device count
     */
    public function status(Request $request): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $activeDevices = $this->subscriptionModel->getActiveForUser((int)$userId);

        return $this->json([
            'success'        => true,
            'is_subscribed'  => !empty($activeDevices),
            'device_count'   => count($activeDevices),
            'devices'        => array_map(function ($d) {
                return [
                    'id'           => (int)$d['id'],
                    'device_name'  => $d['device_name'] ?: 'Browser Device',
                    'created_at'   => $d['created_at'],
                    'last_used_at' => $d['last_used_at'],
                ];
            }, $activeDevices),
        ]);
    }

    /**
     * List all registered devices for current user
     */
    public function listDevices(Request $request): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $devices = $this->subscriptionModel->getActiveForUser((int)$userId);

        $sanitized = array_map(function ($d) {
            return [
                'id'           => (int)$d['id'],
                'device_name'  => $d['device_name'] ?: 'Browser Device',
                'created_at'   => $d['created_at'],
                'last_used_at' => $d['last_used_at'],
            ];
        }, $devices);

        return $this->json([
            'success' => true,
            'devices' => $sanitized
        ]);
    }

    /**
     * Remove / revoke a specific device subscription
     */
    public function removeDevice(Request $request, array $params = []): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $deviceId = (int)($params['id'] ?? $request->input('device_id') ?? 0);
        if ($deviceId <= 0) {
            return $this->json(['success' => false, 'error' => 'Invalid device ID.'], 400);
        }

        // Verify ownership and delete/revoke
        $deleted = $this->subscriptionModel->deleteSubscription($deviceId, (int)$userId);
        if ($deleted) {
            Logger::info("User #{$userId} removed push device #{$deviceId}");
            return $this->json([
                'success' => true,
                'message' => 'Device removed successfully.'
            ]);
        }

        return $this->json([
            'success' => false,
            'error'   => 'Device not found or not owned by you.'
        ], 404);
    }

    /**
     * Send test notification to user's registered devices
     */
    public function testNotification(Request $request): Response
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $activeDevices = $this->subscriptionModel->getActiveForUser((int)$userId);
        if (empty($activeDevices)) {
            return $this->json([
                'success' => false,
                'error'   => 'No active device subscriptions found for this account. Please enable notifications on this device first.'
            ], 400);
        }

        $result = $this->pushService->sendTest((int)$userId);

        return $this->json([
            'success' => $result['success'],
            'sent'    => $result['sent'],
            'failed'  => $result['failed'],
            'message' => $result['sent'] > 0
                ? "Test notification sent to {$result['sent']} active device(s)."
                : "Failed to deliver test notification: " . ($result['error'] ?? 'Endpoint unreachable'),
        ]);
    }

    /**
     * Render Office Notification Settings Page
     */
    public function officeSettings(Request $request): Response
    {
        $userId = (int)Session::getUserId();
        $devices = $this->subscriptionModel->getActiveForUser($userId);
        $user = Session::getUser();

        return $this->render('office/settings/notifications', [
            'title'       => 'Notification Settings',
            'devices'     => $devices,
            'user'        => $user,
            'userRole'    => Session::getUserRole(),
            'vapidPubKey' => $this->pushService->getVapidPublicKey(),
        ], 'layouts/office');
    }

    /**
     * Render Driver Notification Settings Page
     */
    public function driverSettings(Request $request): Response
    {
        $userId = (int)Session::getUserId();
        $devices = $this->subscriptionModel->getActiveForUser($userId);
        $user = Session::getUser();

        return $this->render('driver/notification_settings', [
            'title'       => 'Notification Settings',
            'devices'     => $devices,
            'user'        => $user,
            'userRole'    => 'driver',
            'vapidPubKey' => $this->pushService->getVapidPublicKey(),
        ], 'layouts/driver');
    }

    /**
     * Parse human-readable device name from User-Agent
     */
    private function parseDeviceName(string $ua): string
    {
        $browser = 'Browser';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) {
            $browser = 'Safari';
        }

        $os = 'Device';
        if (str_contains($ua, 'Windows NT 10.0')) {
            $os = 'Windows 10/11';
        } elseif (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone')) {
            $os = 'iPhone';
        } elseif (str_contains($ua, 'iPad')) {
            $os = 'iPad';
        } elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        }

        return "{$browser} on {$os}";
    }
}
