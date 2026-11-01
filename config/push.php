<?php
/**
 * Web Push and VAPID Configuration
 * Progressive Web App & Push Notification Services
 */

return [
    'vapid' => [
        'subject'     => getenv('VAPID_SUBJECT') ?: 'mailto:contact@manaswitravels.com',
        'public_key'  => getenv('VAPID_PUBLIC_KEY') ?: 'BKRPfj0HXF3qQb7UBwXXniO4BPnAaOOjTC1YTgJaq9BGStZGuV8AeijT41PxrIT7iDh-lhrceAEpQQ3Y6FNgRYc',
        'private_key' => getenv('VAPID_PRIVATE_KEY') ?: 'wIkREA0Wtrpx5UOINi1QNSdKs_MKarUIMolgxc35mP4',
    ],

    // Default branding for Push Notifications
    'branding' => [
        'app_name'    => 'Tours',
        'icon'        => '/assets/icons/icon-192.png',
        'badge'       => '/assets/icons/icon-192.png',
    ],

    // Guzzle HTTP Request timeout for push dispatch (seconds)
    'timeout' => 15,
];
