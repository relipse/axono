<?php

namespace App\Policies;

use App\Models\PostSchedule;
use App\Models\User;

class PostSchedulePolicy
{
    public function view(User $user, PostSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }

    public function update(User $user, PostSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }

    public function delete(User $user, PostSchedule $schedule): bool
    {
        return $user->id === $schedule->user_id;
    }
}
