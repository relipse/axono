@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    @if($plan)
        <p class="text-sm text-gray-500 mt-1">
            Current plan: <span class="font-semibold text-indigo-600">{{ $plan->name }}</span>
            @if($subscription->status === 'trial')
                <span class="text-yellow-600">(Trial - ends {{ $subscription->trial_ends_at->diffForHumans() }})</span>
            @endif
        </p>
    @else
        <div class="mt-2 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md">
            You don't have an active subscription. <a href="{{ route('subscription.plans') }}" class="font-semibold underline">Choose a plan</a> to get started.
        </div>
    @endif
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Total Posts</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_posts'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Published</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['published_posts'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Scheduled</p>
        <p class="text-2xl font-bold text-blue-600">{{ $stats['scheduled_posts'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Failed</p>
        <p class="text-2xl font-bold text-red-600">{{ $stats['failed_posts'] }}</p>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Social Accounts</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['social_accounts'] }}</p>
        @if($plan)
            <p class="text-xs text-gray-400">of {{ $plan->max_social_accounts }} allowed</p>
        @endif
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Active Schedules</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_schedules'] }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-500">Data Files</p>
        <p class="text-2xl font-bold text-gray-900">{{ $stats['data_files'] }}</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="flex flex-wrap gap-3 mb-8">
    <a href="{{ route('posts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">New Post</a>
    <a href="{{ route('social-accounts.create') }}" class="bg-white text-indigo-600 border border-indigo-600 px-4 py-2 rounded-md text-sm hover:bg-indigo-50">Connect Account</a>
    <a href="{{ route('schedules.create') }}" class="bg-white text-indigo-600 border border-indigo-600 px-4 py-2 rounded-md text-sm hover:bg-indigo-50">New Schedule</a>
</div>

{{-- Recent Posts --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Posts</h2>
    </div>
    @if($recentPosts->isEmpty())
        <div class="px-6 py-8 text-center text-gray-500">
            No posts yet. <a href="{{ route('posts.create') }}" class="text-indigo-600 hover:underline">Create your first post</a>.
        </div>
    @else
        <div class="divide-y divide-gray-200">
            @foreach($recentPosts as $post)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 truncate">{{ Str::limit($post->content, 80) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $post->socialAccount?->platformLabel() }} &middot;
                            @if($post->status === 'published')
                                Published {{ $post->published_at->diffForHumans() }}
                            @elseif($post->status === 'scheduled')
                                Scheduled for {{ $post->scheduled_at->format('M j, Y g:i A') }}
                            @else
                                {{ ucfirst($post->status) }}
                            @endif
                        </p>
                    </div>
                    <span class="ml-4 px-2 py-1 text-xs rounded-full
                        {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $post->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $post->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $post->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $post->status === 'publishing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    ">
                        {{ ucfirst($post->status) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Recent Activity --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
    </div>
    @if($recentActivity->isEmpty())
        <div class="px-6 py-8 text-center text-gray-500">
            No recent activity.
        </div>
    @else
        <div class="divide-y divide-gray-200">
            @foreach($recentActivity as $log)
                <div class="px-6 py-3">
                    <p class="text-sm text-gray-700">{{ str_replace('.', ' ', $log->action) }}</p>
                    <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
