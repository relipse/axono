<?php

namespace App\Console\Commands;

use App\Services\ScheduleProcessor;
use Illuminate\Console\Command;

class ProcessPostSchedules extends Command
{
    protected $signature = 'schedules:process';
    protected $description = 'Process due post schedules and create posts from data files';

    public function handle(ScheduleProcessor $processor): int
    {
        $this->info('Processing due schedules...');

        $count = $processor->processDueSchedules();

        $this->info("Created {$count} posts from schedules.");

        return self::SUCCESS;
    }
}
