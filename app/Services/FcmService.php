<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $credentialsFile;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id');
        $this->credentialsFile = config('services.firebase.credentials_file');
    }

    /**
     * Send a push notification to a single FCM token.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($token) || empty($this->projectId) || empty($this->credentialsFile)) {
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'data' => array_map('strval', array_merge(['title' => $title, 'body' => $body], $data)),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound'        => 'default',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => ['sound' => 'default'],
                            ],
                        ],
                        'webpush' => [
                            'fcm_options' => [
                                'link' => config('app.url'),
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('FCM: envoi échoué', [
                    'token'  => substr($token, 0, 20) . '...',
                    'status' => $response->status(),
                    'error'  => $response->json('error.message'),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FCM: exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get a cached Google OAuth2 access token using the service account credentials.
     * The token is valid for 1 hour; we cache it for 55 minutes.
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 55 * 60, function () {
            if (!file_exists($this->credentialsFile)) {
                throw new \RuntimeException("Firebase credentials file not found: {$this->credentialsFile}");
            }

            $keyData = json_decode(file_get_contents($this->credentialsFile), true);

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $keyData
            );

            $token = $credentials->fetchAuthToken();

            if (empty($token['access_token'])) {
                throw new \RuntimeException('FCM: impossible d\'obtenir un access token Google');
            }

            return $token['access_token'];
        });
    }
}
