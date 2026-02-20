@extends('layouts.app')
@section('title', 'View Post')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Post Details</h1>
        <a href="{{ route('posts.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">&larr; Back to Posts</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        <div>
            <span class="px-2 py-1 text-xs rounded-full
                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                {{ $post->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $post->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                {{ $post->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                {{ $post->status === 'publishing' ? 'bg-yellow-100 text-yellow-800' : '' }}
            ">{{ ucfirst($post->status) }}</span>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Platform</h3>
            <p class="text-gray-900">{{ $post->socialAccount?->platformLabel() }} ({{ $post->socialAccount?->username }})</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Content</h3>
            <p class="text-gray-900 whitespace-pre-wrap">{{ $post->content }}</p>
        </div>

        @if($post->scheduled_at)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Scheduled For</h3>
                <p class="text-gray-900">{{ $post->scheduled_at->format('M j, Y g:i A') }}</p>
            </div>
        @endif

        @if($post->published_at)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Published At</h3>
                <p class="text-gray-900">{{ $post->published_at->format('M j, Y g:i A') }}</p>
            </div>
        @endif

        @if($post->platform_post_id)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Platform Post ID</h3>
                <p class="text-gray-900 font-mono text-sm">{{ $post->platform_post_id }}</p>
            </div>
        @endif

        @if($post->error_message)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                <strong>Error:</strong> {{ $post->error_message }}
                <br><span class="text-xs">Retry count: {{ $post->retry_count }}</span>
            </div>
        @endif

        <div class="flex space-x-3 pt-4 border-t border-gray-200">
            @if(in_array($post->status, ['draft', 'scheduled', 'failed']))
                <a href="{{ route('posts.edit', $post) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Edit</a>
            @endif
            <form method="POST" action="{{ route('posts.destroy', $post) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700" onclick="return confirm('Delete this post?')">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
