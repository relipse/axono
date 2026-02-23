<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CW Voice">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <title>Claude Worker - @yield('title', 'Admin')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
    /* ── Claude Worker standalone overrides ─────────────────────────── */
    .cw-nav-brand {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--pf-gray-900);
        text-decoration: none;
        letter-spacing: -0.025em;
    }
    .cw-nav-brand:hover { color: var(--pf-gray-900); }
    .cw-nav-brand-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--pf-primary-600), var(--pf-primary-800));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cw-nav-brand-sub {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--pf-gray-400);
        letter-spacing: 0.02em;
    }
    </style>
</head>
<body>
    <nav class="pf-nav">
        <div class="pf-container">
            <div class="pf-nav-inner">
                <div class="pf-flex pf-items-center pf-gap-4">
                    <a href="{{ route('claude-worker.marketing') }}" class="cw-nav-brand">
                        <span class="cw-nav-brand-icon">
                            <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <span>
                            Claude Worker
                            <span class="cw-nav-brand-sub" style="display:block">Automated Code Tasks</span>
                        </span>
                    </a>
                    <div class="pf-nav-links pf-md-hidden">
                        <a href="{{ route('claude-worker.marketing') }}" class="pf-nav-link {{ request()->routeIs('claude-worker.marketing') ? 'active' : '' }}">Features</a>
                        @if(session('cw_admin'))
                            <a href="{{ route('claude-worker.index') }}" class="pf-nav-link {{ request()->routeIs('claude-worker.index') ? 'active' : '' }}">Admin</a>
                            <a href="{{ route('claude-worker.voice') }}" class="pf-nav-link {{ request()->routeIs('claude-worker.voice') ? 'active' : '' }}">Voice</a>
                        @endif
                    </div>
                </div>
                <div class="pf-nav-right">
                    @if(session('cw_admin'))
                        <span class="pf-nav-user">Admin</span>
                        <form method="POST" action="{{ route('claude-worker.logout') }}">
                            @csrf
                            <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('claude-worker.login') }}" class="pf-btn pf-btn-primary pf-btn-sm">Admin Login</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="pf-container pf-mt-4">
        @if(session('success'))
            <div class="pf-alert pf-alert-success">{{ session('success') }}</div>
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

    <main class="pf-container" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        @yield('content')
    </main>

    <footer class="pf-footer">
        <div class="pf-container">
            &copy; {{ date('Y') }} Claude Worker. Powered by Claude Code.
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
