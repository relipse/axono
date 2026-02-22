<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    {{-- Navigation --}}
    <nav class="pf-nav">
        <div class="pf-container">
            <div class="pf-nav-inner">
                <div class="pf-flex pf-items-center pf-gap-4">
                    <a href="{{ route('home') }}" class="pf-nav-brand">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="28" height="28" rx="7" fill="currentColor"/>
                            <path d="M8 14L12 18L20 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ config('app.name') }}
                    </a>
                    @auth
                        <div class="pf-nav-links pf-md-hidden">
                            <a href="{{ route('dashboard') }}" class="pf-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                            <a href="{{ route('posts.index') }}" class="pf-nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}">Posts</a>
                            <a href="{{ route('schedules.index') }}" class="pf-nav-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}">Schedules</a>
                            <a href="{{ route('social-accounts.index') }}" class="pf-nav-link {{ request()->routeIs('social-accounts.*') ? 'active' : '' }}">Accounts</a>
                            @if(auth()->user()->subscriptionPlan()?->can_upload_files)
                                <a href="{{ route('data-files.index') }}" class="pf-nav-link {{ request()->routeIs('data-files.*') ? 'active' : '' }}">Data Files</a>
                            @endif
                            <a href="{{ route('claude-worker.index') }}" class="pf-nav-link {{ request()->routeIs('claude-worker.*') ? 'active' : '' }}">Claude Worker</a>
                        </div>
                    @endauth
                </div>
                <div class="pf-nav-right">
                    @auth
                        <a href="{{ route('subscription.plans') }}" class="pf-nav-link">Plans</a>
                        <span class="pf-nav-user">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="pf-nav-link">Login</a>
                        <a href="{{ route('register') }}" class="pf-btn pf-btn-primary pf-btn-sm">Sign Up</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="pf-container pf-mt-4">
        @if(session('success'))
            <div class="pf-alert pf-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="pf-alert pf-alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="pf-alert pf-alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="pf-alert pf-alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="pf-container" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="pf-footer">
        <div class="pf-container">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
