<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class DataFile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'total_lines',
        'used_lines',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PostSchedule::class);
    }

    public function getLines(): array
    {
        $content = Storage::get($this->file_path);
        if (!$content) return [];

        return array_values(array_filter(
            explode("\n", $content),
            fn($line) => trim($line) !== ''
        ));
    }

    public function getLineAt(int $index): ?string
    {
        $lines = $this->getLines();
        return $lines[$index] ?? null;
    }

    public function getLinesFrom(int $start, int $count): array
    {
        $lines = $this->getLines();
        return array_slice($lines, $start, $count);
    }

    public function hasRemainingLines(int $fromLine): bool
    {
        return $fromLine < $this->total_lines;
    }

    public function remainingLineCount(int $fromLine): int
    {
        return max(0, $this->total_lines - $fromLine);
    }
}
