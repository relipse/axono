@extends('layouts.app')
@section('title', 'Connect Social Account')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Connect Social Account</h1>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md mb-6 text-sm">
        To connect a social account, you'll need API credentials from the respective platform.
        Enter your access tokens below. In a production environment, this would use OAuth.
    </div>

    <form method="POST" action="{{ route('social-accounts.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf

        <div>
            <label for="platform" class="block text-sm font-medium text-gray-700">Platform</label>
            <select id="platform" name="platform" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                <option value="">Select a platform...</option>
                <option value="twitter" {{ old('platform') === 'twitter' ? 'selected' : '' }}>Twitter / X</option>
                <option value="facebook" {{ old('platform') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="linkedin" {{ old('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                <option value="instagram" {{ old('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
            </select>
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username / Handle</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                placeholder="@yourhandle">
        </div>

        <div>
            <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name (optional)</label>
            <input id="display_name" type="text" name="display_name" value="{{ old('display_name') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="platform_user_id" class="block text-sm font-medium text-gray-700">Platform User ID (optional)</label>
            <input id="platform_user_id" type="text" name="platform_user_id" value="{{ old('platform_user_id') }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                placeholder="Used for Facebook pages and LinkedIn">
        </div>

        <div>
            <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token</label>
            <input id="access_token" type="password" name="access_token" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="refresh_token" class="block text-sm font-medium text-gray-700">Refresh Token (optional)</label>
            <input id="refresh_token" type="password" name="refresh_token"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('social-accounts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Connect Account</button>
        </div>
    </form>
</div>
@endsection
