<?php
namespace App\Services;

use App\Models\PushSubscription;
use App\Core\Config;
use App\Core\Logger;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    private PushSubscription $subscriptionModel;
    private ?WebPush $webPush = null;
    private array $vapidConfig;
    private array $branding;

    public function __construct()
    {
        $this->subscriptionModel = new PushSubscription();
        $this->vapidConfig = Config::get('push.vapid', []);
        $this->branding = Config::get('push.branding', [
            'app_name' => 'Tours',
            'icon'     => '/assets/icons/icon-192.png',
            'badge'    => '/assets/icons/icon-192.png',
        ]);

        $this->ensureOpenSslConfig();
    }

    /**
     * Ensure OpenSSL config is located on Windows environments
     */
    private function ensureOpenSslConfig(): void
    {
        if (DIRECTORY_SEPARATOR === '\\' && !getenv('OPENSSL_CONF')) {
            $candidates = [
                dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf',
                'E:/tools/php/extras/ssl/openssl.cnf',
                'E:/xampp/php/extras/ssl/openssl.cnf',
                'C:/xampp/php/extras/ssl/openssl.cnf',
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    putenv("OPENSSL_CONF={$cand}");
                    $_ENV['OPENSSL_CONF'] = $cand;
                    break;
                }
            }
        }
    }

    /**
     * Get or initialize WebPush client
     */
    private function getWebPush(): ?WebPush
    {
        if ($this->webPush !== null) {
            return $this->webPush;
        }

        $subject    = $this->vapidConfig['subject'] ?? 'mailto:contact@manaswitravels.com';
        $publicKey  = $this->vapidConfig['public_key'] ?? '';
        $privateKey = $this->vapidConfig['private_key'] ?? '';

        if (empty($publicKey) || empty($privateKey)) {
            Logger::error("WebPush initialization failed: missing VAPID keys in configuration.");
            return null;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject'    => $subject,
                    'publicKey'  => $publicKey,
                    'privateKey' => $privateKey,
                ]
            ];

            $timeout = (int)Config::get('push.timeout', 15);
            $options = [
                'timeout' => $timeout,
            ];

            $this->webPush = new WebPush($auth, $options);
            // Default to automatic flush
            $this->webPush->setAutomaticPadding(false);

            return $this->webPush;
        } catch (\Throwable $e) {
            Logger::error("Failed to initialize WebPush service: " . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
            return null;
        }
    }

    /**
     * Return public VAPID key for frontend registration
     */
    public function getVapidPublicKey(): string
    {
        return $this->vapidConfig['public_key'] ?? '';
    }

    /**
     * Build safe, branded notification payload
     */
    public function buildPayload(string $title, string $body, ?string $url = null, array $extra = []): string
    {
        $appName = $this->branding['app_name'] ?? 'Tours';
        $displayTitle = str_contains($title, $appName) ? $title : "{$appName} • {$title}";

        // Normalize URL to relative or absolute path
        $destinationUrl = $url ?: '/';
        if (!preg_match('#^https?://#i', $destinationUrl) && !str_starts_with($destinationUrl, '/')) {
            $destinationUrl = '/' . $destinationUrl;
        }

        $payloadData = [
            'title'     => $displayTitle,
            'body'      => $body,
            'icon'      => $extra['icon'] ?? $this->branding['icon'] ?? '/assets/icons/icon-192.png',
            'badge'     => $extra['badge'] ?? $this->branding['badge'] ?? '/assets/icons/icon-192.png',
            'url'       => $destinationUrl,
            'tag'       => $extra['tag'] ?? ('tours-' . time() . '-' . mt_rand(100, 999)),
            'timestamp' => time() * 1000,
            'requireInteraction' => $extra['requireInteraction'] ?? false,
            'data'      => array_merge(['url' => $destinationUrl], $extra['data'] ?? []),
        ];

        return json_encode($payloadData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Send Web Push notification to all active devices of a single user
     */
    public function sendToUser(int $userId, string $title, string $body, ?string $url = null, array $extra = []): array
    {
        $subscriptions = $this->subscriptionModel->getActiveForUser($userId);
        if (empty($subscriptions)) {
            return [
                'success' => true,
                'sent'    => 0,
                'failed'  => 0,
                'devices' => 0,
                'message' => 'No active push subscriptions found for user.'
            ];
        }

        return $this->dispatchNotifications($subscriptions, $title, $body, $url, $extra);
    }

    /**
     * Send Web Push notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, ?string $url = null, array $extra = []): array
    {
        $subscriptions = $this->subscriptionModel->getActiveForUsers($userIds);
        if (empty($subscriptions)) {
            return [
                'success' => true,
                'sent'    => 0,
                'failed'  => 0,
                'devices' => 0,
                'message' => 'No active push subscriptions found for recipients.'
            ];
        }

        return $this->dispatchNotifications($subscriptions, $title, $body, $url, $extra);
    }

    /**
     * Send a test notification to a specific user subscription ID or user's devices
     */
    public function sendTest(int $userId): array
    {
        return $this->sendToUser(
            $userId,
            'Notification Test Successful',
            'Tours progressive notifications are working properly on this device.',
            '/driver/dashboard',
            [
                'tag' => 'tours-test-' . time(),
                'requireInteraction' => false
            ]
        );
    }

    /**
     * Dispatch notification payload to a list of subscription records
     */
    private function dispatchNotifications(array $subscriptions, string $title, string $body, ?string $url = null, array $extra = []): array
    {
        $webPush = $this->getWebPush();
        if (!$webPush) {
            return [
                'success' => false,
                'sent'    => 0,
                'failed'  => count($subscriptions),
                'error'   => 'WebPush service could not be initialized'
            ];
        }

        $payload = $this->buildPayload($title, $body, $url, $extra);

        $sentCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($subscriptions as $subRecord) {
            try {
                $sub = Subscription::create([
                    'endpoint'        => $subRecord['endpoint'],
                    'publicKey'       => $subRecord['p256dh'],
                    'authToken'       => $subRecord['auth'],
                    'contentEncoding' => $subRecord['content_encoding'] ?: 'aes128gcm',
                ]);

                $report = $webPush->sendOneNotification($sub, $payload);

                if ($report->isSuccess()) {
                    $sentCount++;
                    $this->subscriptionModel->markUsed((int)$subRecord['id']);
                    $results[] = [
                        'device_id' => $subRecord['id'],
                        'status'    => 'delivered',
                    ];
                } else {
                    $failedCount++;
                    $reason = $report->getReason();
                    $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;

                    // Subscription is expired, unsubscribed, or gone (404 or 410)
                    if ($report->isSubscriptionExpired() || $statusCode === 404 || $statusCode === 410) {
                        $this->subscriptionModel->revoke((int)$subRecord['id']);
                        Logger::info("Revoked expired push subscription #{$subRecord['id']} (HTTP {$statusCode})");
                    } else {
                        Logger::warning("Push delivery failed for subscription #{$subRecord['id']}: {$reason} (Status: {$statusCode})");
                    }

                    $results[] = [
                        'device_id' => $subRecord['id'],
                        'status'    => 'failed',
                        'code'      => $statusCode,
                        'reason'    => $reason,
                    ];
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Logger::error("Push notification exception for subscription #{$subRecord['id']}: " . $e->getMessage());
                $results[] = [
                    'device_id' => $subRecord['id'],
                    'status'    => 'error',
                    'error'     => $e->getMessage()
                ];
            }
        }

        return [
            'success' => $sentCount > 0 || $failedCount === 0,
            'sent'    => $sentCount,
            'failed'  => $failedCount,
            'total'   => count($subscriptions),
            'results' => $results,
        ];
    }
}
