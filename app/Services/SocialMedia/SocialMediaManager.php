<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;
use InvalidArgumentException;

class SocialMediaManager
{
    public function driver(string $platform): SocialMediaInterface
    {
        return match ($platform) {
            'twitter' => new TwitterService(),
            'facebook' => new FacebookService(),
            'linkedin' => new LinkedInService(),
            default => throw new InvalidArgumentException("Unsupported platform: {$platform}"),
        };
    }

    public function publish(SocialAccount $account, string $content, ?string $imagePath = null): array
    {
        return $this->driver($account->platform)->publish($account, $content, $imagePath);
    }

    public function deletePost(SocialAccount $account, string $platformPostId): bool
    {
        return $this->driver($account->platform)->deletePost($account, $platformPostId);
    }

    public function verifyCredentials(SocialAccount $account): bool
    {
        return $this->driver($account->platform)->verifyCredentials($account);
    }
}
