@extends('layouts.app')
@section('title', 'Edit Post')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Post</h1>

    <form method="POST" action="{{ route('posts.update', $post) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="social_account_id" class="block text-sm font-medium text-gray-700">Social Account</label>
            <select id="social_account_id" name="social_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('social_account_id', $post->social_account_id) == $account->id ? 'selected' : '' }}>
                        {{ $account->platformLabel() }} - {{ $account->username }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
            <textarea id="content" name="content" rows="5" required maxlength="5000"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">{{ old('content', $post->content) }}</textarea>
        </div>

        <div>
            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule For (optional)</label>
            <input id="scheduled_at" type="datetime-local" name="scheduled_at"
                value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        @if($post->error_message)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                <strong>Last Error:</strong> {{ $post->error_message }}
            </div>
        @endif

        <div class="flex justify-end space-x-3">
            <a href="{{ route('posts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Post</button>
        </div>
    </form>
</div>
@endsection
