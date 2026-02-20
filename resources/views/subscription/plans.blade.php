@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="pf-text-center pf-mb-8">
    <h1 class="pf-text-2xl pf-font-bold">Choose Your Plan</h1>
    <p class="pf-text-muted pf-mt-2">Start with a 14-day free trial on any plan.</p>
</div>

<div class="pf-grid pf-grid-3 pf-max-w-5xl">
    @foreach($plans as $plan)
        <div class="pf-pricing-card {{ $plan->slug === 'professional' ? 'featured' : '' }}">
            @if($plan->slug === 'professional')
                <span class="pf-pricing-badge">Most Popular</span>
            @endif

            <h2 class="pf-pricing-name">{{ $plan->name }}</h2>
            <div class="pf-pricing-price">
                <span class="amount">${{ number_format($plan->price, 2) }}</span>
                <span class="period">/{{ $plan->billing_cycle }}</span>
            </div>

            <p class="pf-pricing-desc">{{ $plan->description }}</p>

            <ul class="pf-pricing-features">
                <li>
                    <svg class="check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->max_social_accounts }} social accounts
                </li>
                <li>
                    <svg class="check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->max_posts_per_day }} posts per day
                </li>
                <li>
                    <svg class="check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    {{ $plan->max_scheduled_posts }} scheduled posts
                </li>
                <li>
                    @if($plan->can_upload_files)
                        <svg class="check" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        File uploads (up to {{ $plan->max_file_size_mb }}MB)
                    @else
                        <svg class="cross" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="disabled">No file uploads</span>
                    @endif
                </li>
            </ul>

            @auth
                @if($currentSubscription && $currentSubscription->subscription_plan_id === $plan->id)
                    <div class="pf-text-center">
                        <span class="pf-badge pf-badge-green" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Current Plan</span>
                    </div>
                @else
                    <form method="POST" action="{{ route('subscription.subscribe', $plan) }}">
                        @csrf
                        <button type="submit" class="pf-btn {{ $plan->slug === 'professional' ? 'pf-btn-primary' : 'pf-btn-secondary' }} pf-w-full">
                            {{ $currentSubscription ? 'Switch to ' . $plan->name : 'Start Free Trial' }}
                        </button>
                    </form>
                @endif
            @else
                <a href="{{ route('register') }}" class="pf-btn {{ $plan->slug === 'professional' ? 'pf-btn-primary' : 'pf-btn-secondary' }} pf-w-full">
                    Get Started
                </a>
            @endauth
        </div>
    @endforeach
</div>

@auth
    @if($currentSubscription)
        <div class="pf-text-center pf-mt-8">
            <form method="POST" action="{{ route('subscription.cancel') }}">
                @csrf
                <button type="submit" class="pf-btn pf-btn-ghost pf-text-danger" onclick="return confirm('Are you sure you want to cancel your subscription?')">
                    Cancel Subscription
                </button>
            </form>
        </div>
    @endif
@endauth
@endsection
