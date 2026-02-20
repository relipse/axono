<?php

namespace App\Console\Commands;

use App\Services\PostPublisher;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    protected $signature = 'posts:publish-due';
    protected $description = 'Publish all scheduled posts that are due';

    public function handle(PostPublisher $publisher): int
    {
        $this->info('Processing due posts...');

        $count = $publisher->publishDuePosts();

        $this->info("Published {$count} posts.");

        return self::SUCCESS;
    }
}
