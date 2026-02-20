@extends('layouts.app')
@section('title', 'View Post')

@section('content')
<div class="pf-max-w-2xl">
    <div class="pf-page-header">
        <h1>Post Details</h1>
        <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-ghost pf-btn-sm">&larr; Back to Posts</a>
    </div>

    <div class="pf-card">
        <div class="pf-card-body">
            <div class="pf-mb-4">
                <span class="pf-badge
                    {{ $post->status === 'published' ? 'pf-badge-green' : '' }}
                    {{ $post->status === 'scheduled' ? 'pf-badge-blue' : '' }}
                    {{ $post->status === 'failed' ? 'pf-badge-red' : '' }}
                    {{ $post->status === 'draft' ? 'pf-badge-gray' : '' }}
                    {{ $post->status === 'publishing' ? 'pf-badge-yellow' : '' }}
                ">{{ ucfirst($post->status) }}</span>
            </div>

            <div class="pf-mb-4">
                <h3 class="pf-label">Platform</h3>
                <p>{{ $post->socialAccount?->platformLabel() }} ({{ $post->socialAccount?->username }})</p>
            </div>

            <div class="pf-mb-4">
                <h3 class="pf-label">Content</h3>
                <p class="pf-whitespace-pre">{{ $post->content }}</p>
            </div>

            @if($post->scheduled_at)
                <div class="pf-mb-4">
                    <h3 class="pf-label">Scheduled For</h3>
                    <p>{{ $post->scheduled_at->format('M j, Y g:i A') }}</p>
                </div>
            @endif

            @if($post->published_at)
                <div class="pf-mb-4">
                    <h3 class="pf-label">Published At</h3>
                    <p>{{ $post->published_at->format('M j, Y g:i A') }}</p>
                </div>
            @endif

            @if($post->platform_post_id)
                <div class="pf-mb-4">
                    <h3 class="pf-label">Platform Post ID</h3>
                    <p class="pf-font-mono pf-text-sm">{{ $post->platform_post_id }}</p>
                </div>
            @endif

            @if($post->error_message)
                <div class="pf-alert pf-alert-danger">
                    <strong>Error:</strong> {{ $post->error_message }}
                    <br><span class="pf-text-xs">Retry count: {{ $post->retry_count }}</span>
                </div>
            @endif

            <div class="pf-flex pf-gap-3 pf-mt-6" style="padding-top: 1rem; border-top: 1px solid var(--pf-gray-200);">
                @if(in_array($post->status, ['draft', 'scheduled', 'failed']))
                    <a href="{{ route('posts.edit', $post) }}" class="pf-btn pf-btn-primary">Edit</a>
                @endif
                <form method="POST" action="{{ route('posts.destroy', $post) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="pf-btn pf-btn-danger" onclick="return confirm('Delete this post?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
