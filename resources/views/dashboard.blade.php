@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="pf-mb-6">
    <h1 class="pf-text-2xl pf-font-bold">Dashboard</h1>
    @if($plan)
        <p class="pf-text-sm pf-text-muted pf-mt-1">
            Current plan: <span class="pf-font-semibold" style="color: var(--pf-primary-600);">{{ $plan->name }}</span>
            @if($subscription->status === 'trial')
                <span style="color: var(--pf-warning-700);">(Trial - ends {{ $subscription->trial_ends_at->diffForHumans() }})</span>
            @endif
        </p>
    @else
        <div class="pf-alert pf-alert-warning pf-mt-2">
            You don't have an active subscription. <a href="{{ route('subscription.plans') }}" class="pf-font-semibold" style="text-decoration: underline;">Choose a plan</a> to get started.
        </div>
    @endif
</div>

{{-- Stats Grid --}}
<div class="pf-grid pf-grid-4 pf-mb-8">
    <div class="pf-stat-card">
        <p class="pf-stat-label">Total Posts</p>
        <p class="pf-stat-value">{{ $stats['total_posts'] }}</p>
    </div>
    <div class="pf-stat-card">
        <p class="pf-stat-label">Published</p>
        <p class="pf-stat-value green">{{ $stats['published_posts'] }}</p>
    </div>
    <div class="pf-stat-card">
        <p class="pf-stat-label">Scheduled</p>
        <p class="pf-stat-value blue">{{ $stats['scheduled_posts'] }}</p>
    </div>
    <div class="pf-stat-card">
        <p class="pf-stat-label">Failed</p>
        <p class="pf-stat-value red">{{ $stats['failed_posts'] }}</p>
    </div>
</div>

<div class="pf-grid pf-grid-3 pf-mb-8">
    <div class="pf-stat-card">
        <p class="pf-stat-label">Social Accounts</p>
        <p class="pf-stat-value">{{ $stats['social_accounts'] }}</p>
        @if($plan)
            <p class="pf-stat-note">of {{ $plan->max_social_accounts }} allowed</p>
        @endif
    </div>
    <div class="pf-stat-card">
        <p class="pf-stat-label">Active Schedules</p>
        <p class="pf-stat-value">{{ $stats['active_schedules'] }}</p>
    </div>
    <div class="pf-stat-card">
        <p class="pf-stat-label">Data Files</p>
        <p class="pf-stat-value">{{ $stats['data_files'] }}</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="pf-actions-bar">
    <a href="{{ route('posts.create') }}" class="pf-btn pf-btn-primary">New Post</a>
    <a href="{{ route('social-accounts.create') }}" class="pf-btn pf-btn-secondary">Connect Account</a>
    <a href="{{ route('schedules.create') }}" class="pf-btn pf-btn-secondary">New Schedule</a>
</div>

{{-- Recent Posts --}}
<div class="pf-card pf-mb-8">
    <div class="pf-card-header">
        <h2>Recent Posts</h2>
    </div>
    @if($recentPosts->isEmpty())
        <div class="pf-card-body pf-text-center pf-text-muted">
            No posts yet. <a href="{{ route('posts.create') }}">Create your first post</a>.
        </div>
    @else
        @foreach($recentPosts as $post)
            <div class="pf-list-item">
                <div style="flex: 1; min-width: 0;">
                    <p class="pf-text-sm pf-truncate">{{ Str::limit($post->content, 80) }}</p>
                    <p class="pf-text-xs pf-text-muted pf-mt-1">
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
                <span class="pf-badge
                    {{ $post->status === 'published' ? 'pf-badge-green' : '' }}
                    {{ $post->status === 'scheduled' ? 'pf-badge-blue' : '' }}
                    {{ $post->status === 'failed' ? 'pf-badge-red' : '' }}
                    {{ $post->status === 'draft' ? 'pf-badge-gray' : '' }}
                    {{ $post->status === 'publishing' ? 'pf-badge-yellow' : '' }}
                ">
                    {{ ucfirst($post->status) }}
                </span>
            </div>
        @endforeach
    @endif
</div>

{{-- Recent Activity --}}
<div class="pf-card">
    <div class="pf-card-header">
        <h2>Recent Activity</h2>
    </div>
    @if($recentActivity->isEmpty())
        <div class="pf-card-body pf-text-center pf-text-muted">
            No recent activity.
        </div>
    @else
        @foreach($recentActivity as $log)
            <div class="pf-list-item" style="flex-direction: column; align-items: flex-start;">
                <p class="pf-text-sm">{{ str_replace('.', ' ', $log->action) }}</p>
                <p class="pf-text-xs pf-text-muted">{{ $log->created_at->diffForHumans() }}</p>
            </div>
        @endforeach
    @endif
</div>
@endsection
