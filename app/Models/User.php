<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_claude_worker_admin' => 'boolean',
        ];
    }

    /**
     * Check if this user can access the Claude Worker admin panel.
     * Access is granted if the user has the DB flag set, OR if their
     * email is listed in the CLAUDE_WORKER_ADMIN_EMAILS env variable.
     */
    public function isClaudeWorkerAdmin(): bool
    {
        if ($this->is_claude_worker_admin) {
            return true;
        }

        $envEmails = config('claude-worker.admin_emails', []);
        if (!empty($envEmails) && in_array($this->email, $envEmails, true)) {
            return true;
        }

        return false;
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)->where('status', 'active')->latest();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function postSchedules(): HasMany
    {
        return $this->hasMany(PostSchedule::class);
    }

    public function dataFiles(): HasMany
    {
        return $this->hasMany(DataFile::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription !== null;
    }

    public function subscriptionPlan(): ?SubscriptionPlan
    {
        return $this->subscription?->plan;
    }

    public function canAddSocialAccount(): bool
    {
        $plan = $this->subscriptionPlan();
        if (!$plan) return false;
        return $this->socialAccounts()->where('is_active', true)->count() < $plan->max_social_accounts;
    }

    public function canPostToday(): bool
    {
        $plan = $this->subscriptionPlan();
        if (!$plan) return false;
        $todayPosts = $this->posts()
            ->whereDate('published_at', today())
            ->where('status', 'published')
            ->count();
        return $todayPosts < $plan->max_posts_per_day;
    }

    public function canScheduleMore(): bool
    {
        $plan = $this->subscriptionPlan();
        if (!$plan) return false;
        $scheduledPosts = $this->posts()
            ->where('status', 'scheduled')
            ->count();
        return $scheduledPosts < $plan->max_scheduled_posts;
    }
}
