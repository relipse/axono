<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'social_account_id',
        'content',
        'image_path',
        'status',
        'scheduled_at',
        'published_at',
        'platform_post_id',
        'error_message',
        'retry_count',
        'data_file_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
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

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDue($query)
    {
        return $query->scheduled()->where('scheduled_at', '<=', now());
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsPublished(string $platformPostId = null): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'platform_post_id' => $platformPostId,
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}
