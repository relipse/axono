@extends('layouts.app')
@section('title', 'Create Post')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Post</h1>

    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf

        <div>
            <label for="social_account_id" class="block text-sm font-medium text-gray-700">Social Account</label>
            <select id="social_account_id" name="social_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                <option value="">Select an account...</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('social_account_id') == $account->id ? 'selected' : '' }}>
                        {{ $account->platformLabel() }} - {{ $account->username }}
                    </option>
                @endforeach
            </select>
            @if($accounts->isEmpty())
                <p class="text-sm text-yellow-600 mt-1">No social accounts connected. <a href="{{ route('social-accounts.create') }}" class="underline">Connect one first</a>.</p>
            @endif
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
            <textarea id="content" name="content" rows="5" required maxlength="5000"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                placeholder="What's on your mind?">{{ old('content') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Max 5000 characters</p>
        </div>

        <div>
            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule For (optional)</label>
            <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
            <p class="text-xs text-gray-400 mt-1">Leave empty to save as draft</p>
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700">Image (optional)</label>
            <input id="image" type="file" name="image" accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('posts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Create Post</button>
        </div>
    </form>
</div>
@endsection
