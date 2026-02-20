<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\PostSchedule;
use Illuminate\Support\Facades\Log;

class ScheduleProcessor
{
    public function processDueSchedules(): int
    {
        $schedules = PostSchedule::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', now());
            })
            ->with(['user', 'socialAccount', 'dataFile'])
            ->get();

        $totalCreated = 0;

        foreach ($schedules as $schedule) {
            $created = $this->processSchedule($schedule);
            $totalCreated += $created;
        }

        return $totalCreated;
    }

    public function processSchedule(PostSchedule $schedule): int
    {
        if (!$schedule->dataFile) {
            Log::warning("Schedule {$schedule->id} has no data file");
            $schedule->update(['is_active' => false]);
            return 0;
        }

        if (!$schedule->socialAccount || !$schedule->socialAccount->is_active) {
            Log::warning("Schedule {$schedule->id} has no active social account");
            return 0;
        }

        if (!$schedule->user->canScheduleMore()) {
            Log::info("User {$schedule->user_id} reached scheduled post limit");
            return 0;
        }

        $dataFile = $schedule->dataFile;
        $lines = $dataFile->getLinesFrom($schedule->current_line, $schedule->posts_per_run);

        if (empty($lines)) {
            Log::info("Schedule {$schedule->id} has no remaining lines in data file");
            $schedule->update(['is_active' => false]);
            return 0;
        }

        $created = 0;

        foreach ($lines as $line) {
            $content = trim($line);
            if (empty($content)) continue;

            Post::create([
                'user_id' => $schedule->user_id,
                'social_account_id' => $schedule->social_account_id,
                'content' => $content,
                'status' => 'scheduled',
                'scheduled_at' => now(),
                'data_file_id' => $dataFile->id,
            ]);

            $created++;
        }

        $newLine = $schedule->current_line + count($lines);
        $schedule->update(['current_line' => $newLine]);
        $dataFile->update(['used_lines' => $newLine]);

        $schedule->calculateNextRun();

        ActivityLog::log($schedule->user, 'schedule.processed', $schedule, [
            'posts_created' => $created,
            'current_line' => $newLine,
        ]);

        Log::info("Schedule {$schedule->id} processed: {$created} posts created");

        return $created;
    }
}
