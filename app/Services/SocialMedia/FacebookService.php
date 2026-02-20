<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService implements SocialMediaInterface
{
    private string $apiBaseUrl = 'https://graph.facebook.com/v18.0';

    public function publish(SocialAccount $account, string $content, ?string $imagePath = null): array
    {
        try {
            $endpoint = "{$this->apiBaseUrl}/{$account->platform_user_id}/feed";
            $params = [
                'message' => $content,
                'access_token' => $account->access_token,
            ];

            if ($imagePath) {
                $endpoint = "{$this->apiBaseUrl}/{$account->platform_user_id}/photos";
                $params['url'] = $imagePath;
            }

            $response = Http::post($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'platform_post_id' => $data['id'] ?? $data['post_id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'Failed to publish to Facebook'),
            ];
        } catch (\Exception $e) {
            Log::error('Facebook publish error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deletePost(SocialAccount $account, string $platformPostId): bool
    {
        try {
            $response = Http::delete("{$this->apiBaseUrl}/{$platformPostId}", [
                'access_token' => $account->access_token,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Facebook delete error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function verifyCredentials(SocialAccount $account): bool
    {
        try {
            $response = Http::get("{$this->apiBaseUrl}/me", [
                'access_token' => $account->access_token,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
