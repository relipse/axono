@extends('layouts.app')
@section('title', 'Home')

@section('content')
<div class="text-center py-16">
    <h1 class="text-5xl font-bold text-gray-900 mb-4">Automate Your Social Media</h1>
    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
        Schedule posts, upload data files, and let cron jobs handle the publishing.
        Manage Twitter, Facebook, LinkedIn, and Instagram from one dashboard.
    </p>
    <div class="flex justify-center space-x-4">
        @auth
            <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-700">
                Go to Dashboard
            </a>
        @else
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-700">
                Get Started Free
            </a>
            <a href="{{ route('subscription.plans') }}" class="bg-white text-indigo-600 border-2 border-indigo-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-50">
                View Plans
            </a>
        @endauth
    </div>
</div>

{{-- Features --}}
<div class="grid md:grid-cols-3 gap-8 mt-12">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold mb-2">Schedule Posts</h3>
        <p class="text-gray-600">Create posts and schedule them for the perfect time. Set up recurring schedules with cron expressions for hands-free automation.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold mb-2">Upload Data Files</h3>
        <p class="text-gray-600">Upload text files containing your post content. Each line becomes a separate post, automatically published on your schedule.</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold mb-2">Multi-Platform</h3>
        <p class="text-gray-600">Connect Twitter/X, Facebook, LinkedIn, and Instagram. Publish the same content across all your social channels.</p>
    </div>
</div>
@endsection
