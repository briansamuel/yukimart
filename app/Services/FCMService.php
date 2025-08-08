<?php

namespace App\Services;

use App\Models\FCMToken;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FCMService
{
    private $serviceAccountPath;
    private $projectId;
    private $fcmUrl = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';
    private $accessToken;

    public function __construct()
    {
        $this->serviceAccountPath = config('services.fcm.service_account_path');
        $this->projectId = config('services.fcm.project_id');
    }

    /**
     * Get OAuth2 access token using Service Account.
     */
    private function getAccessToken()
    {
        if ($this->accessToken && $this->isTokenValid()) {
            return $this->accessToken;
        }

        try {
            $serviceAccount = $this->getServiceAccountCredentials();

            // Create JWT
            $now = time();
            $payload = [
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600, // 1 hour
            ];

            $jwt = JWT::encode($payload, $serviceAccount['private_key'], 'RS256');

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $this->accessToken = $tokenData['access_token'];

                // Cache token with expiry
                cache(['fcm_access_token' => $this->accessToken], now()->addMinutes(50));

                return $this->accessToken;
            } else {
                throw new \Exception('Failed to get access token: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('FCM access token error: ' . $e->getMessage());
            throw new \Exception('Failed to authenticate with FCM: ' . $e->getMessage());
        }
    }

    /**
     * Get Service Account credentials.
     */
    private function getServiceAccountCredentials()
    {
        if (!$this->serviceAccountPath) {
            throw new \Exception('FCM Service Account path not configured');
        }

        if (!file_exists($this->serviceAccountPath)) {
            throw new \Exception('FCM Service Account file not found: ' . $this->serviceAccountPath);
        }

        $credentials = json_decode(file_get_contents($this->serviceAccountPath), true);

        if (!$credentials || !isset($credentials['private_key']) || !isset($credentials['client_email'])) {
            throw new \Exception('Invalid Service Account file format');
        }

        return $credentials;
    }

    /**
     * Check if current access token is valid.
     */
    private function isTokenValid()
    {
        return cache('fcm_access_token') === $this->accessToken;
    }

    /**
     * Convert all data values to strings for FCM v1 API.
     */
    private function convertDataToStrings(array $data)
    {
        $stringData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $stringData[$key] = json_encode($value);
            } elseif (is_bool($value)) {
                $stringData[$key] = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $stringData[$key] = '';
            } else {
                $stringData[$key] = (string)$value;
            }
        }

        return $stringData;
    }

    /**
     * Send notification to specific tokens using FCM v1 API.
     */
    public function sendToTokens(array $tokens, array $notification, array $data = [])
    {
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No tokens provided'];
        }

        try {
            $accessToken = $this->getAccessToken();
            $url = str_replace('{project_id}', $this->projectId, $this->fcmUrl);

            $successCount = 0;
            $failureCount = 0;
            $results = [];

            // FCM v1 API requires individual requests for each token
            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $notification['title'],
                            'body' => $notification['message'],
                            'image' => $notification['icon'] ?? null,
                        ],
                        'data' => $this->convertDataToStrings(array_merge($data, [
                            'notification_id' => (string)($notification['id'] ?? ''),
                            'type' => $notification['type'] ?? 'general',
                            'priority' => $notification['priority'] ?? 'normal',
                            'action_url' => $notification['action_url'] ?? '',
                            'action_text' => $notification['action_text'] ?? '',
                        ])),
                        'android' => [
                            'priority' => $this->mapAndroidPriority($notification['priority'] ?? 'normal'),
                            'ttl' => '86400s', // 24 hours
                            'notification' => [
                                'icon' => $notification['icon'] ?? 'default',
                                'sound' => 'default',
                                'click_action' => $notification['action_url'] ?? null,
                            ]
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => $this->mapApnsPriority($notification['priority'] ?? 'normal'),
                                'apns-expiration' => (string)(time() + 86400), // 24 hours
                            ],
                            'payload' => [
                                'aps' => [
                                    'alert' => [
                                        'title' => $notification['title'],
                                        'body' => $notification['message'],
                                    ],
                                    'sound' => 'default',
                                    'badge' => 1,
                                ]
                            ]
                        ],
                        'webpush' => [
                            'headers' => [
                                'TTL' => '86400', // 24 hours
                            ],
                            'notification' => [
                                'title' => $notification['title'],
                                'body' => $notification['message'],
                                'icon' => $notification['icon'] ?? '/favicon.ico',
                                'badge' => '/favicon.ico',
                                'tag' => 'yukimart-notification',
                                'requireInteraction' => in_array($notification['priority'] ?? 'normal', ['high', 'urgent']),
                                'actions' => ($notification['action_url'] ?? null) ? [
                                    [
                                        'action' => 'open',
                                        'title' => $notification['action_text'] ?? 'Open',
                                        'icon' => '/favicon.ico'
                                    ]
                                ] : []
                            ]
                        ]
                    ]
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);

                if ($response->successful()) {
                    $successCount++;
                    $results[] = ['token' => $token, 'success' => true];
                } else {
                    $failureCount++;
                    $error = $response->json();
                    $results[] = [
                        'token' => $token,
                        'success' => false,
                        'error' => $error['error']['message'] ?? 'Unknown error'
                    ];

                    // Handle invalid tokens
                    if (isset($error['error']['details'])) {
                        $this->handleInvalidTokenV1($token, $error['error']);
                    }
                }
            }

            Log::info('FCM v1 notification sent', [
                'tokens_count' => count($tokens),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

            return [
                'success' => true,
                'sent_count' => $successCount,
                'failed_count' => $failureCount,
                'results' => $results
            ];

        } catch (\Exception $e) {
            Log::error('FCM v1 notification failed', [
                'error' => $e->getMessage(),
                'tokens_count' => count($tokens)
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to specific user.
     */
    public function sendToUser($userId, array $notification, array $data = [])
    {
        $tokens = FCMToken::getActiveTokensForUser($userId);
        
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active tokens for user'];
        }

        return $this->sendToTokens($tokens, $notification, $data);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers(array $userIds, array $notification, array $data = [])
    {
        $tokens = FCMToken::getActiveTokensForUsers($userIds);
        
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active tokens for users'];
        }

        return $this->sendToTokens($tokens, $notification, $data);
    }

    /**
     * Send notification to all users.
     */
    public function sendToAll(array $notification, array $data = [])
    {
        $tokens = FCMToken::getAllActiveTokens();
        
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active tokens'];
        }

        // Split into chunks of 1000 (FCM limit)
        $chunks = array_chunk($tokens, 1000);
        $totalResults = ['success' => true, 'sent_count' => 0, 'failed_count' => 0];

        foreach ($chunks as $chunk) {
            $result = $this->sendToTokens($chunk, $notification, $data);
            if ($result['success']) {
                $totalResults['sent_count'] += $result['sent_count'];
                $totalResults['failed_count'] += $result['failed_count'];
            }
        }

        return $totalResults;
    }

    /**
     * Send notification based on existing notification model.
     */
    public function sendFromNotification(Notification $notification)
    {
        $fcmData = [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'priority' => $notification->priority,
            'action_url' => $notification->action_url,
            'action_text' => $notification->action_text,
            'icon' => $notification->icon,
        ];

        $additionalData = $notification->data ?? [];

        // Check if notification has specific user
        if ($notification->notifiable_type === 'App\Models\User' && $notification->notifiable_id) {
            return $this->sendToUser($notification->notifiable_id, $fcmData, $additionalData);
        } else {
            // Send to all users
            return $this->sendToAll($fcmData, $additionalData);
        }
    }

    /**
     * Queue FCM notification for background processing.
     */
    public function queueNotification(Notification $notification)
    {
        Queue::push('fcm-notification', [
            'notification_id' => $notification->id
        ]);
    }

    /**
     * Map priority levels to Android FCM priority.
     */
    private function mapAndroidPriority($priority)
    {
        return match($priority) {
            'urgent', 'high' => 'high',
            'normal', 'low' => 'normal',
            default => 'normal'
        };
    }

    /**
     * Map priority levels to APNS priority.
     */
    private function mapApnsPriority($priority)
    {
        return match($priority) {
            'urgent', 'high' => '10',
            'normal', 'low' => '5',
            default => '5'
        };
    }

    /**
     * Map priority levels to FCM priority (legacy).
     */
    private function mapPriority($priority)
    {
        return match($priority) {
            'urgent', 'high' => 'high',
            'normal', 'low' => 'normal',
            default => 'normal'
        };
    }

    /**
     * Handle invalid tokens from FCM v1 response.
     */
    private function handleInvalidTokenV1($token, $error)
    {
        $errorCode = $error['status'] ?? '';
        $errorMessage = $error['message'] ?? '';

        // Deactivate invalid tokens based on FCM v1 error codes
        $invalidCodes = [
            'NOT_FOUND',
            'INVALID_ARGUMENT',
            'UNREGISTERED',
            'SENDER_ID_MISMATCH'
        ];

        if (in_array($errorCode, $invalidCodes)) {
            FCMToken::where('token', $token)->update(['is_active' => false]);
            Log::info('Deactivated invalid FCM token (v1)', [
                'token' => substr($token, 0, 20) . '...',
                'error_code' => $errorCode,
                'error_message' => $errorMessage
            ]);
        }
    }

    /**
     * Handle invalid tokens from FCM legacy response.
     */
    private function handleInvalidTokens(array $tokens, array $results)
    {
        foreach ($results as $index => $result) {
            if (isset($result['error'])) {
                $token = $tokens[$index] ?? null;
                if ($token) {
                    $errorType = $result['error'];

                    // Deactivate invalid tokens
                    if (in_array($errorType, ['NotRegistered', 'InvalidRegistration'])) {
                        FCMToken::where('token', $token)->update(['is_active' => false]);
                        Log::info('Deactivated invalid FCM token', ['token' => substr($token, 0, 20) . '...', 'error' => $errorType]);
                    }
                }
            }
        }
    }

    /**
     * Test FCM configuration using Service Account.
     */
    public function testConfiguration()
    {
        try {
            // Test Service Account file
            $serviceAccount = $this->getServiceAccountCredentials();

            if (!$serviceAccount) {
                return ['success' => false, 'message' => 'Service Account credentials not found'];
            }

            // Test access token generation
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return ['success' => false, 'message' => 'Failed to generate access token'];
            }

            // Test FCM v1 API with dry run
            $url = str_replace('{project_id}', $this->projectId, $this->fcmUrl);

            $testPayload = [
                'message' => [
                    'token' => 'dummy_token_for_test',
                    'notification' => [
                        'title' => 'Test Notification',
                        'body' => 'This is a test notification',
                    ],
                    'data' => [
                        'test' => 'true'
                    ]
                ],
                'validate_only' => true // Dry run - don't actually send
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $testPayload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'FCM Service Account configuration is valid',
                    'details' => [
                        'project_id' => $this->projectId,
                        'service_account_email' => $serviceAccount['client_email'] ?? 'Unknown',
                        'api_version' => 'v1'
                    ]
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'message' => 'FCM API test failed: ' . ($error['error']['message'] ?? 'Unknown error')
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'FCM configuration test failed: ' . $e->getMessage()
            ];
        }
    }
}
