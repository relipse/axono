<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Post;
use App\Services\SocialMedia\SocialMediaManager;
use Illuminate\Support\Facades\Log;

class PostPublisher
{
    public function __construct(
        private SocialMediaManager $socialMedia
    ) {}

    public function publish(Post $post): bool
    {
        $post->update(['status' => 'publishing']);

        $account = $post->socialAccount;
        if (!$account || !$account->is_active) {
            $post->markAsFailed('Social account is inactive or missing');
            return false;
        }

        $result = $this->socialMedia->publish($account, $post->content, $post->image_path);

        if ($result['success']) {
            $post->markAsPublished($result['platform_post_id'] ?? null);

            ActivityLog::log($post->user, 'post.published', $post, [
                'platform' => $account->platform,
                'platform_post_id' => $result['platform_post_id'] ?? null,
            ]);

            Log::info("Post {$post->id} published successfully to {$account->platform}");
            return true;
        }

        $post->markAsFailed($result['error'] ?? 'Unknown error');

        ActivityLog::log($post->user, 'post.failed', $post, [
            'platform' => $account->platform,
            'error' => $result['error'] ?? 'Unknown error',
        ]);

        Log::warning("Post {$post->id} failed to publish", ['error' => $result['error']]);
        return false;
    }

    public function publishDuePosts(): int
    {
        $duePosts = Post::due()
            ->with(['socialAccount', 'user'])
            ->where('retry_count', '<', 3)
            ->get();

        $published = 0;

        foreach ($duePosts as $post) {
            if (!$post->user->canPostToday()) {
                Log::info("User {$post->user_id} reached daily post limit, skipping post {$post->id}");
                continue;
            }

            if ($this->publish($post)) {
                $published++;
            }
        }

        return $published;
    }
}
