@extends('layouts.app')
@section('title', 'Posts')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Posts</h1>
    <a href="{{ route('posts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">New Post</a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('posts.index') }}" class="flex flex-wrap gap-4">
        <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2 border">
            <option value="">All Statuses</option>
            @foreach(['draft', 'scheduled', 'publishing', 'published', 'failed'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="platform" class="rounded-md border-gray-300 shadow-sm text-sm px-3 py-2 border">
            <option value="">All Platforms</option>
            @foreach(['twitter', 'facebook', 'linkedin', 'instagram'] as $platform)
                <option value="{{ $platform }}" {{ request('platform') === $platform ? 'selected' : '' }}>{{ ucfirst($platform) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-200">Filter</button>
        @if(request()->hasAny(['status', 'platform']))
            <a href="{{ route('posts.index') }}" class="text-gray-500 px-4 py-2 text-sm hover:text-gray-700">Clear</a>
        @endif
    </form>
</div>

{{-- Posts Table --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    @if($posts->isEmpty())
        <div class="px-6 py-12 text-center text-gray-500">
            <p>No posts found.</p>
            <a href="{{ route('posts.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Create your first post</a>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Content</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Platform</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($posts as $post)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ Str::limit($post->content, 60) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $post->socialAccount?->platformLabel() }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $post->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $post->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $post->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $post->status === 'publishing' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            ">{{ ucfirst($post->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $post->scheduled_at ? $post->scheduled_at->format('M j, Y g:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <a href="{{ route('posts.show', $post) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                            @if(in_array($post->status, ['draft', 'scheduled', 'failed']))
                                <a href="{{ route('posts.edit', $post) }}" class="text-gray-600 hover:text-gray-800">Edit</a>
                            @endif
                            <form method="POST" action="{{ route('posts.destroy', $post) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this post?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
