<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_posts' => $user->posts()->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'scheduled_posts' => $user->posts()->where('status', 'scheduled')->count(),
            'failed_posts' => $user->posts()->where('status', 'failed')->count(),
            'social_accounts' => $user->socialAccounts()->where('is_active', true)->count(),
            'active_schedules' => $user->postSchedules()->where('is_active', true)->count(),
            'data_files' => $user->dataFiles()->count(),
        ];

        $recentPosts = $user->posts()
            ->with('socialAccount')
            ->latest()
            ->take(10)
            ->get();

        $recentActivity = $user->activityLogs()
            ->latest()
            ->take(15)
            ->get();

        $subscription = $user->subscription;
        $plan = $subscription?->plan;

        return view('dashboard', compact('stats', 'recentPosts', 'recentActivity', 'subscription', 'plan'));
    }
}
