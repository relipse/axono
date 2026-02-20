@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="text-center mb-10">
    <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
    <p class="text-gray-600 mt-2">Start with a 14-day free trial on any plan.</p>
</div>

<div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
    @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-sm border-2 {{ $plan->slug === 'professional' ? 'border-indigo-500' : 'border-gray-200' }} p-6 flex flex-col">
            @if($plan->slug === 'professional')
                <div class="text-center mb-2">
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                </div>
            @endif

            <h2 class="text-xl font-bold text-gray-900 text-center">{{ $plan->name }}</h2>
            <div class="text-center my-4">
                <span class="text-4xl font-bold text-gray-900">${{ number_format($plan->price, 2) }}</span>
                <span class="text-gray-500">/{{ $plan->billing_cycle }}</span>
            </div>

            <p class="text-gray-600 text-sm text-center mb-6">{{ $plan->description }}</p>

            <ul class="space-y-3 mb-8 flex-1">
                <li class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    {{ $plan->max_social_accounts }} social accounts
                </li>
                <li class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    {{ $plan->max_posts_per_day }} posts per day
                </li>
                <li class="flex items-center text-sm text-gray-700">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    {{ $plan->max_scheduled_posts }} scheduled posts
                </li>
                <li class="flex items-center text-sm text-gray-700">
                    @if($plan->can_upload_files)
                        <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        File uploads (up to {{ $plan->max_file_size_mb }}MB)
                    @else
                        <svg class="w-4 h-4 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-400">No file uploads</span>
                    @endif
                </li>
            </ul>

            @auth
                @if($currentSubscription && $currentSubscription->subscription_plan_id === $plan->id)
                    <div class="text-center">
                        <span class="bg-green-100 text-green-800 px-4 py-2 rounded-md text-sm font-semibold">Current Plan</span>
                    </div>
                @else
                    <form method="POST" action="{{ route('subscription.subscribe', $plan) }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $plan->slug === 'professional' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-800 hover:bg-gray-900' }}">
                            {{ $currentSubscription ? 'Switch to ' . $plan->name : 'Start Free Trial' }}
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('register') }}" class="w-full block text-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $plan->slug === 'professional' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-800 hover:bg-gray-900' }}">
                    Get Started
                </a>
            @endauth
        </div>
    @endforeach
</div>

@auth
    @if($currentSubscription)
        <div class="text-center mt-8">
            <form method="POST" action="{{ route('subscription.cancel') }}">
                @csrf
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Are you sure you want to cancel your subscription?')">
                    Cancel Subscription
                </button>
            </form>
        </div>
    @endif
@endauth
@endsection
