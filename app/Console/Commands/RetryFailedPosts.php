<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\PostPublisher;
use Illuminate\Console\Command;

class RetryFailedPosts extends Command
{
    protected $signature = 'posts:retry-failed {--max-retries=3 : Maximum retry attempts}';
    protected $description = 'Retry publishing failed posts';

    public function handle(PostPublisher $publisher): int
    {
        $maxRetries = (int) $this->option('max-retries');

        $failedPosts = Post::failed()
            ->where('retry_count', '<', $maxRetries)
            ->with(['socialAccount', 'user'])
            ->get();

        $this->info("Found {$failedPosts->count()} failed posts to retry.");

        $retried = 0;
        foreach ($failedPosts as $post) {
            $post->update(['status' => 'scheduled', 'scheduled_at' => now()]);
            if ($publisher->publish($post)) {
                $retried++;
            }
        }

        $this->info("Successfully retried {$retried} posts.");

        return self::SUCCESS;
    }
}
