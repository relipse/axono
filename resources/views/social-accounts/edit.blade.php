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
            <label for="username" class="block text-sm font-medium text-gray-700">Username / Handle</label>
            <input id="username" type="text" name="username" value="{{ old('username', $socialAccount->username) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
            <input id="display_name" type="text" name="display_name" value="{{ old('display_name', $socialAccount->display_name) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token (leave blank to keep current)</label>
            <input id="access_token" type="password" name="access_token"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="refresh_token" class="block text-sm font-medium text-gray-700">Refresh Token (leave blank to keep current)</label>
            <input id="refresh_token" type="password" name="refresh_token"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div class="flex items-center">
            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $socialAccount->is_active) ? 'checked' : '' }}
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('social-accounts.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Account</button>
        </div>
    </form>
</div>
@endsection
