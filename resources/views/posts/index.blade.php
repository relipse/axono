@extends('layouts.app')
@section('title', 'Posts')

@section('content')
<div class="pf-page-header">
    <h1>Posts</h1>
    <a href="{{ route('posts.create') }}" class="pf-btn pf-btn-primary">New Post</a>
</div>

{{-- Filters --}}
<div class="pf-card pf-mb-6">
    <div class="pf-card-body">
        <form method="GET" action="{{ route('posts.index') }}" class="pf-flex pf-gap-4" style="flex-wrap: wrap;">
            <select name="status" class="pf-select" style="width: auto;">
                <option value="">All Statuses</option>
                @foreach(['draft', 'scheduled', 'publishing', 'published', 'failed'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="platform" class="pf-select" style="width: auto;">
                <option value="">All Platforms</option>
                @foreach(['twitter', 'facebook', 'linkedin', 'instagram'] as $platform)
                    <option value="{{ $platform }}" {{ request('platform') === $platform ? 'selected' : '' }}>{{ ucfirst($platform) }}</option>
                @endforeach
            </select>
            <button type="submit" class="pf-btn pf-btn-secondary">Filter</button>
            @if(request()->hasAny(['status', 'platform']))
                <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-ghost">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Posts Table --}}
<div class="pf-card">
    @if($posts->isEmpty())
        <div class="pf-card-body pf-text-center pf-text-muted">
            <p>No posts found.</p>
            <a href="{{ route('posts.create') }}" class="pf-mt-2 pf-inline-block">Create your first post</a>
        </div>
    @else
        <table class="pf-table">
            <thead>
                <tr>
                    <th>Content</th>
                    <th>Platform</th>
                    <th>Status</th>
                    <th>Scheduled</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td class="pf-truncate" style="max-width: 20rem;">{{ Str::limit($post->content, 60) }}</td>
                        <td>{{ $post->socialAccount?->platformLabel() }}</td>
                        <td>
                            <span class="pf-badge
                                {{ $post->status === 'published' ? 'pf-badge-green' : '' }}
                                {{ $post->status === 'scheduled' ? 'pf-badge-blue' : '' }}
                                {{ $post->status === 'failed' ? 'pf-badge-red' : '' }}
                                {{ $post->status === 'draft' ? 'pf-badge-gray' : '' }}
                                {{ $post->status === 'publishing' ? 'pf-badge-yellow' : '' }}
                            ">{{ ucfirst($post->status) }}</span>
                        </td>
                        <td>
                            {{ $post->scheduled_at ? $post->scheduled_at->format('M j, Y g:i A') : '-' }}
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('posts.show', $post) }}" class="pf-btn pf-btn-ghost pf-btn-sm">View</a>
                            @if(in_array($post->status, ['draft', 'scheduled', 'failed']))
                                <a href="{{ route('posts.edit', $post) }}" class="pf-btn pf-btn-ghost pf-btn-sm">Edit</a>
                            @endif
                            <form method="POST" action="{{ route('posts.destroy', $post) }}" class="pf-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger" onclick="return confirm('Delete this post?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pf-pagination">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
