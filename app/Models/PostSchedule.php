<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Cron\CronExpression;

class PostSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'social_account_id',
        'data_file_id',
        'cron_expression',
        'timezone',
        'is_active',
        'posts_per_run',
        'current_line',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function dataFile(): BelongsTo
    {
        return $this->belongsTo(DataFile::class);
    }

    public function isDue(): bool
    {
        if (!$this->is_active) return false;
        if (!$this->next_run_at) return true;
        return $this->next_run_at->isPast();
    }

    public function calculateNextRun(): void
    {
        $cron = new CronExpression($this->cron_expression);
        $this->update([
            'next_run_at' => $cron->getNextRunDate(now($this->timezone)),
            'last_run_at' => now(),
        ]);
    }
}
