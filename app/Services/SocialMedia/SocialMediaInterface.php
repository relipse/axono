<?php

namespace App\Services\SocialMedia;

use App\Models\SocialAccount;

interface SocialMediaInterface
{
    public function publish(SocialAccount $account, string $content, ?string $imagePath = null): array;

    public function deletePost(SocialAccount $account, string $platformPostId): bool;

    public function verifyCredentials(SocialAccount $account): bool;
}
