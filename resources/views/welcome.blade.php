@extends('layouts.app')
@section('title', 'Home')

@section('content')
<div class="pf-hero">
    <h1>Automate Your Social Media</h1>
    <p>
        Schedule posts, upload data files, and let cron jobs handle the publishing.
        Manage Twitter, Facebook, LinkedIn, and Instagram from one dashboard.
    </p>
    <div class="pf-hero-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="pf-btn pf-btn-primary pf-btn-lg">
                Go to Dashboard
            </a>
        @else
            <a href="{{ route('register') }}" class="pf-btn pf-btn-primary pf-btn-lg">
                Get Started Free
            </a>
            <a href="{{ route('subscription.plans') }}" class="pf-btn pf-btn-secondary pf-btn-lg">
                View Plans
            </a>
        @endauth
    </div>
</div>

{{-- Features --}}
<div class="pf-grid pf-grid-3 pf-mt-8">
    <div class="pf-feature-card">
        <div class="pf-feature-icon" style="background: var(--pf-primary-50); color: var(--pf-primary-600);">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h3>Schedule Posts</h3>
        <p>Create posts and schedule them for the perfect time. Set up recurring schedules with cron expressions for hands-free automation.</p>
    </div>
    <div class="pf-feature-card">
        <div class="pf-feature-icon" style="background: var(--pf-success-50); color: var(--pf-success-500);">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        </div>
        <h3>Upload Data Files</h3>
        <p>Upload text files containing your post content. Each line becomes a separate post, automatically published on your schedule.</p>
    </div>
    <div class="pf-feature-card">
        <div class="pf-feature-icon" style="background: var(--pf-warning-50); color: var(--pf-warning-500);">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3>Multi-Platform</h3>
        <p>Connect Twitter/X, Facebook, LinkedIn, and Instagram. Publish the same content across all your social channels.</p>
    </div>
</div>
@endsection
