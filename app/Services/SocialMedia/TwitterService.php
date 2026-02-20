<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService implements SocialMediaInterface
{
    private string $apiBaseUrl = 'https://api.twitter.com/2';

    public function publish(SocialAccount $account, string $content, ?string $imagePath = null): array
    {
        try {
            $response = Http::withToken($account->access_token)
                ->post("{$this->apiBaseUrl}/tweets", [
                    'text' => $content,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'platform_post_id' => $data['data']['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('detail', 'Failed to publish tweet'),
            ];
        } catch (\Exception $e) {
            Log::error('Twitter publish error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deletePost(SocialAccount $account, string $platformPostId): bool
    {
        try {
            $response = Http::withToken($account->access_token)
                ->delete("{$this->apiBaseUrl}/tweets/{$platformPostId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Twitter delete error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function verifyCredentials(SocialAccount $account): bool
    {
        try {
            $response = Http::withToken($account->access_token)
                ->get("{$this->apiBaseUrl}/users/me");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
