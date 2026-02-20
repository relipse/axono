<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('schedules:process')->everyFiveMinutes();
Schedule::command('posts:publish-due')->everyMinute();
Schedule::command('posts:retry-failed')->hourly();
