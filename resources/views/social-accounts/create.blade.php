@extends('layouts.app')
@section('title', 'Connect Social Account')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Connect Social Account</h1>

    <p class="text-gray-600 mb-6">Click a platform below to authorize via OAuth. You'll be redirected to the platform to grant access, then returned here automatically.</p>

    <div class="space-y-4">
        {{-- Twitter / X --}}
        <div class="bg-white rounded-lg shadow-sm p-5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">X</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Twitter / X</h3>
                    <p class="text-sm text-gray-500">Post tweets on your behalf</p>
                </div>
            </div>
            @if(in_array('twitter', $connectedPlatforms))
                <span class="text-sm text-green-600 font-medium">Connected</span>
            @else
                <a href="{{ route('social.redirect', 'twitter') }}"
                   class="bg-black text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-800">
                    Connect
                </a>
            @endif
        </div>

        {{-- Facebook --}}
        <div class="bg-white rounded-lg shadow-sm p-5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">f</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Facebook</h3>
                    <p class="text-sm text-gray-500">Post to your pages and profile</p>
                </div>
            </div>
            @if(in_array('facebook', $connectedPlatforms))
                <span class="text-sm text-green-600 font-medium">Connected</span>
            @else
                <a href="{{ route('social.redirect', 'facebook') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                    Connect
                </a>
            @endif
        </div>

        {{-- LinkedIn --}}
        <div class="bg-white rounded-lg shadow-sm p-5 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-blue-700 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">in</span>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">LinkedIn</h3>
                    <p class="text-sm text-gray-500">Share professional updates</p>
                </div>
            </div>
            @if(in_array('linkedin', $connectedPlatforms))
                <span class="text-sm text-green-600 font-medium">Connected</span>
            @else
                <a href="{{ route('social.redirect', 'linkedin') }}"
                   class="bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-800">
                    Connect
                </a>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('social-accounts.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">&larr; Back to Accounts</a>
    </div>
</div>
@endsection
