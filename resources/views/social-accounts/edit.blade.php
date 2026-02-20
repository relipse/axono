@extends('layouts.app')
@section('title', 'Edit Social Account')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Social Account</h1>

    <form method="POST" action="{{ route('social-accounts.update', $socialAccount) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Platform</label>
            <p class="mt-1 text-gray-900">{{ $socialAccount->platformLabel() }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Username</label>
            <p class="mt-1 text-gray-900">{{ '@' . $socialAccount->username }}</p>
            <p class="text-xs text-gray-400 mt-1">Username is set automatically via OAuth</p>
        </div>

        <div>
            <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
            <input id="display_name" type="text" name="display_name" value="{{ old('display_name', $socialAccount->display_name) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Token Status</label>
            @if($socialAccount->isTokenExpired())
                <p class="mt-1 text-red-600 text-sm">Token expired. Please reconnect below.</p>
            @elseif($socialAccount->token_expires_at)
                <p class="mt-1 text-green-600 text-sm">Valid until {{ $socialAccount->token_expires_at->format('M j, Y g:i A') }}</p>
            @else
                <p class="mt-1 text-green-600 text-sm">Active (no expiration)</p>
            @endif
        </div>

        <div class="flex items-center">
            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $socialAccount->is_active) ? 'checked' : '' }}
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <a href="{{ route('social.redirect', $socialAccount->platform) }}"
               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                Reconnect / Refresh Tokens
            </a>
            <div class="space-x-3">
                <a href="{{ route('social-accounts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Account</button>
            </div>
        </div>
    </form>
</div>
@endsection
