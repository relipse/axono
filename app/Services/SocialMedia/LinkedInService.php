<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService implements SocialMediaInterface
{
    private string $apiBaseUrl = 'https://api.linkedin.com/v2';

    public function publish(SocialAccount $account, string $content, ?string $imagePath = null): array
    {
        try {
            $response = Http::withToken($account->access_token)
                ->post("{$this->apiBaseUrl}/ugcPosts", [
                    'author' => "urn:li:person:{$account->platform_user_id}",
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => [
                                'text' => $content,
                            ],
                            'shareMediaCategory' => 'NONE',
                        ],
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'platform_post_id' => $response->header('X-RestLi-Id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Failed to publish to LinkedIn'),
            ];
        } catch (\Exception $e) {
            Log::error('LinkedIn publish error', ['error' => $e->getMessage()]);
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
                ->delete("{$this->apiBaseUrl}/ugcPosts/{$platformPostId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('LinkedIn delete error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function verifyCredentials(SocialAccount $account): bool
    {
        try {
            $response = Http::withToken($account->access_token)
                ->get("{$this->apiBaseUrl}/me");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
