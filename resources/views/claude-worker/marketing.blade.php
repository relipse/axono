@extends('layouts.app')
@section('title', 'Claude Worker — Automated Code Tasks')

@section('content')
{{-- Hero ──────────────────────────────────────────────────────────────── --}}
<div class="cw-mkt-hero">
    <div class="cw-mkt-hero-badge">Powered by Claude Code</div>
    <h1>Ship Code While You Sleep</h1>
    <p>
        Launch Claude Code tasks in isolated Docker containers, review diffs in the browser,
        and push branches — all from a single web admin panel.
    </p>
    <div class="pf-hero-actions">
        @auth
            @if(auth()->user()->isClaudeWorkerAdmin())
                <a href="{{ route('claude-worker.index') }}" class="pf-btn pf-btn-primary pf-btn-xl">Open Admin Panel</a>
            @else
                <a href="{{ route('dashboard') }}" class="pf-btn pf-btn-primary pf-btn-xl">Go to Dashboard</a>
            @endif
        @else
            <a href="{{ route('register') }}" class="pf-btn pf-btn-primary pf-btn-xl">Get Started</a>
            <a href="#screenshots" class="pf-btn pf-btn-secondary pf-btn-xl">See It In Action</a>
        @endauth
    </div>
</div>

{{-- Stats ─────────────────────────────────────────────────────────────── --}}
<div class="cw-mkt-stats">
    <div class="cw-mkt-stat">
        <span class="cw-mkt-stat-value">100%</span>
        <span class="cw-mkt-stat-label">Isolated</span>
    </div>
    <div class="cw-mkt-stat">
        <span class="cw-mkt-stat-value">3</span>
        <span class="cw-mkt-stat-label">Interfaces</span>
    </div>
    <div class="cw-mkt-stat">
        <span class="cw-mkt-stat-value">0</span>
        <span class="cw-mkt-stat-label">Risk to Your Code</span>
    </div>
    <div class="cw-mkt-stat">
        <span class="cw-mkt-stat-value">1</span>
        <span class="cw-mkt-stat-label">Command Install</span>
    </div>
</div>

{{-- Features ──────────────────────────────────────────────────────────── --}}
<section class="cw-mkt-section">
    <h2 class="cw-mkt-section-title">Everything You Need</h2>
    <p class="cw-mkt-section-sub">Three ways to run tasks. One admin panel to rule them all.</p>

    <div class="pf-grid pf-grid-3 pf-mt-6">
        <div class="cw-mkt-feature">
            <div class="cw-mkt-feature-icon" style="background: var(--pf-primary-50); color: var(--pf-primary-600);">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3>Web Admin Panel</h3>
            <p>Launch tasks, monitor progress, review diffs, and manage Docker containers — all from your browser. Admin-only access with role-based security.</p>
        </div>

        <div class="cw-mkt-feature">
            <div class="cw-mkt-feature-icon" style="background: var(--pf-success-50); color: var(--pf-success-500);">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3>CLI &amp; Bash Scripts</h3>
            <p>Run <code>./claude-worker</code> from your terminal. Full control over repos, branches, models, and review workflow — with interactive diff approval.</p>
        </div>

        <div class="cw-mkt-feature">
            <div class="cw-mkt-feature-icon" style="background: var(--pf-warning-50); color: var(--pf-warning-500);">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
            </div>
            <h3>Desktop GUI</h3>
            <p>Point-and-click task launcher with real-time log streaming. Built with Python/Tkinter — runs on Linux, macOS, and Windows.</p>
        </div>
    </div>
</section>

{{-- How It Works ──────────────────────────────────────────────────────── --}}
<section class="cw-mkt-section">
    <h2 class="cw-mkt-section-title">How It Works</h2>
    <p class="cw-mkt-section-sub">From task to merged code in four steps.</p>

    <div class="cw-mkt-steps">
        <div class="cw-mkt-step">
            <div class="cw-mkt-step-num">1</div>
            <div>
                <h4>Describe Your Task</h4>
                <p>Enter a prompt like "Add unit tests for the auth module" and choose a repo (URL or local path).</p>
            </div>
        </div>
        <div class="cw-mkt-step">
            <div class="cw-mkt-step-num">2</div>
            <div>
                <h4>Claude Works in a Container</h4>
                <p>A Docker container spins up with your repo mounted <strong>read-only</strong>. Claude Code runs your task in complete isolation.</p>
            </div>
        </div>
        <div class="cw-mkt-step">
            <div class="cw-mkt-step-num">3</div>
            <div>
                <h4>Review the Diff</h4>
                <p>View syntax-highlighted diffs, summaries, and full logs right in the admin panel. Accept, reject, or iterate.</p>
            </div>
        </div>
        <div class="cw-mkt-step">
            <div class="cw-mkt-step-num">4</div>
            <div>
                <h4>Push or Apply</h4>
                <p>Push the branch directly, apply the patch to your local repo, or transfer via git bundle — your choice.</p>
            </div>
        </div>
    </div>
</section>

{{-- Security ──────────────────────────────────────────────────────────── --}}
<section class="cw-mkt-section">
    <h2 class="cw-mkt-section-title">Built for Safety</h2>
    <p class="cw-mkt-section-sub">Your code is never at risk.</p>

    <div class="pf-grid pf-grid-2 pf-mt-6">
        <div class="cw-mkt-safety-card">
            <div class="cw-mkt-safety-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h4>Read-Only Mounts</h4>
                <p>Local repos are mounted read-only inside the container. Your original files are never modified.</p>
            </div>
        </div>
        <div class="cw-mkt-safety-card">
            <div class="cw-mkt-safety-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h4>Admin-Only Access</h4>
                <p>The web panel is protected by role-based middleware. Only users flagged as Claude Worker admins can access it.</p>
            </div>
        </div>
        <div class="cw-mkt-safety-card">
            <div class="cw-mkt-safety-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h4>Docker Isolation</h4>
                <p>Each task runs in its own ephemeral container. No shared state, no side effects.</p>
            </div>
        </div>
        <div class="cw-mkt-safety-card">
            <div class="cw-mkt-safety-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <h4>Diff Before Merge</h4>
                <p>Nothing gets pushed without your review. Inspect every change with syntax-highlighted diffs.</p>
            </div>
        </div>
    </div>
</section>

{{-- Screenshots ───────────────────────────────────────────────────────── --}}
@if(count($screenshots) > 0)
<section class="cw-mkt-section" id="screenshots">
    <h2 class="cw-mkt-section-title">See It In Action</h2>
    <p class="cw-mkt-section-sub">Screenshots are served live — always showing the latest version.</p>

    <div class="cw-mkt-gallery">
        @foreach($screenshots as $shot)
        <div class="cw-mkt-gallery-item" onclick="cwMktLightbox('{{ $shot['url'] }}', '{{ $shot['label'] }}')">
            <img src="{{ $shot['url'] }}" alt="{{ $shot['label'] }}" loading="lazy">
            <div class="cw-mkt-gallery-label">{{ $shot['label'] }}</div>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- Install ───────────────────────────────────────────────────────────── --}}
<section class="cw-mkt-section">
    <h2 class="cw-mkt-section-title">Install in One Command</h2>
    <p class="cw-mkt-section-sub">Debian, Ubuntu, and derivatives — everything handled automatically.</p>

    <div class="cw-mkt-code-block">
        <div class="cw-mkt-code-header">
            <span class="cw-mkt-code-dot" style="background:#ff5f57"></span>
            <span class="cw-mkt-code-dot" style="background:#febc2e"></span>
            <span class="cw-mkt-code-dot" style="background:#28c840"></span>
            <span class="cw-mkt-code-title">Terminal</span>
        </div>
        <pre class="cw-mkt-code"><code><span class="cw-c"># Clone the repo</span>
git clone https://github.com/relipse/axono.git
<span class="cw-c">cd</span> axono

<span class="cw-c"># Run the one-command installer (Debian/Ubuntu)</span>
sudo ./claude-worker/install-debian.sh

<span class="cw-c"># Or manual setup (any platform with PHP 8.2+)</span>
./claude-worker/setup-server.sh
php artisan serve --host=0.0.0.0 --port=8000</code></pre>
    </div>

    <div class="cw-mkt-install-grid">
        <div class="cw-mkt-install-item">
            <strong>PHP 8.2+</strong>
            <span>with all extensions</span>
        </div>
        <div class="cw-mkt-install-item">
            <strong>Composer</strong>
            <span>latest, verified</span>
        </div>
        <div class="cw-mkt-install-item">
            <strong>Docker CE</strong>
            <span>official repo</span>
        </div>
        <div class="cw-mkt-install-item">
            <strong>SQLite</strong>
            <span>zero-config DB</span>
        </div>
        <div class="cw-mkt-install-item">
            <strong>Apache / Nginx</strong>
            <span>auto-detected</span>
        </div>
        <div class="cw-mkt-install-item">
            <strong>Migrations</strong>
            <span>run automatically</span>
        </div>
    </div>
</section>

{{-- CTA ───────────────────────────────────────────────────────────────── --}}
<div class="cw-mkt-cta">
    <h2>Ready to automate your coding tasks?</h2>
    <p>Set up Claude Worker on your server and start shipping code faster.</p>
    <div class="pf-hero-actions">
        @auth
            @if(auth()->user()->isClaudeWorkerAdmin())
                <a href="{{ route('claude-worker.index') }}" class="pf-btn pf-btn-primary pf-btn-xl">Open Admin Panel</a>
            @endif
        @else
            <a href="{{ route('register') }}" class="pf-btn pf-btn-primary pf-btn-xl">Get Started Free</a>
        @endauth
    </div>
</div>

{{-- Lightbox ──────────────────────────────────────────────────────────── --}}
<div class="cw-mkt-lightbox" id="cwMktLightbox" onclick="this.classList.remove('active')">
    <div class="cw-mkt-lightbox-inner" onclick="event.stopPropagation()">
        <button class="cw-mkt-lightbox-close" onclick="document.getElementById('cwMktLightbox').classList.remove('active')">&times;</button>
        <img id="cwMktLightboxImg" src="" alt="">
        <div class="cw-mkt-lightbox-label" id="cwMktLightboxLabel"></div>
    </div>
</div>
@endsection

@section('scripts')
<style>
/* ── Marketing Page Styles ─────────────────────────────────────────────── */

.cw-mkt-hero {
    text-align: center;
    padding: 4rem 0 3rem;
}

.cw-mkt-hero-badge {
    display: inline-block;
    padding: 0.375rem 1rem;
    background: var(--pf-primary-50);
    color: var(--pf-primary-700);
    font-size: 0.8125rem;
    font-weight: 600;
    border-radius: 9999px;
    margin-bottom: 1.5rem;
    border: 1px solid var(--pf-primary-200);
}

.cw-mkt-hero h1 {
    font-size: 3.5rem;
    font-weight: 800;
    letter-spacing: -0.035em;
    line-height: 1.1;
    background: linear-gradient(135deg, var(--pf-gray-900) 0%, var(--pf-primary-600) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    max-width: 700px;
    margin: 0 auto;
}

.cw-mkt-hero p {
    font-size: 1.25rem;
    color: var(--pf-gray-500);
    max-width: 620px;
    margin: 1.25rem auto 2rem;
    line-height: 1.65;
}

/* ── Stats bar ──────────────────────────────────────────────────────── */
.cw-mkt-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    padding: 2rem 0;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.cw-mkt-stat {
    text-align: center;
}

.cw-mkt-stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    color: var(--pf-primary-600);
    letter-spacing: -0.02em;
}

.cw-mkt-stat-label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--pf-gray-500);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* ── Sections ───────────────────────────────────────────────────────── */
.cw-mkt-section {
    padding: 3rem 0;
}

.cw-mkt-section-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.cw-mkt-section-sub {
    text-align: center;
    color: var(--pf-gray-500);
    font-size: 1.0625rem;
    margin-top: 0.5rem;
}

/* ── Feature cards ──────────────────────────────────────────────────── */
.cw-mkt-feature {
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-xl);
    padding: 2rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.cw-mkt-feature:hover {
    transform: translateY(-4px);
    box-shadow: var(--pf-shadow-lg);
}

.cw-mkt-feature-icon {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: var(--pf-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.cw-mkt-feature h3 {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.cw-mkt-feature p {
    font-size: 0.9375rem;
    color: var(--pf-gray-500);
    line-height: 1.6;
}

.cw-mkt-feature code {
    background: var(--pf-gray-100);
    padding: 0.125rem 0.375rem;
    border-radius: var(--pf-radius-sm);
    font-size: 0.8125rem;
    font-family: 'SF Mono', 'Fira Code', monospace;
    color: var(--pf-primary-700);
}

/* ── How it works steps ─────────────────────────────────────────────── */
.cw-mkt-steps {
    max-width: 640px;
    margin: 2.5rem auto 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.cw-mkt-step {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-lg);
    padding: 1.5rem;
}

.cw-mkt-step-num {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, var(--pf-primary-600), var(--pf-primary-700));
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1rem;
}

.cw-mkt-step h4 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.cw-mkt-step p {
    font-size: 0.9375rem;
    color: var(--pf-gray-500);
    line-height: 1.55;
}

/* ── Safety cards ───────────────────────────────────────────────────── */
.cw-mkt-safety-card {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-lg);
    padding: 1.5rem;
    transition: box-shadow 0.15s;
}

.cw-mkt-safety-card:hover {
    box-shadow: var(--pf-shadow-md);
}

.cw-mkt-safety-icon {
    flex-shrink: 0;
    width: 2.75rem;
    height: 2.75rem;
    background: var(--pf-success-50);
    color: var(--pf-success-500);
    border-radius: var(--pf-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.cw-mkt-safety-card h4 {
    font-size: 0.9375rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.cw-mkt-safety-card p {
    font-size: 0.875rem;
    color: var(--pf-gray-500);
    line-height: 1.55;
}

/* ── Screenshot gallery ─────────────────────────────────────────────── */
.cw-mkt-gallery {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .cw-mkt-gallery { grid-template-columns: 1fr; }
    .cw-mkt-hero h1 { font-size: 2.25rem; }
    .cw-mkt-stats { gap: 1.5rem; }
}

.cw-mkt-gallery-item {
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-lg);
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.cw-mkt-gallery-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--pf-shadow-lg);
}

.cw-mkt-gallery-item img {
    width: 100%;
    display: block;
    border-bottom: 1px solid var(--pf-gray-100);
}

.cw-mkt-gallery-label {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--pf-gray-700);
}

/* ── Code block ─────────────────────────────────────────────────────── */
.cw-mkt-code-block {
    max-width: 640px;
    margin: 2rem auto;
    border-radius: var(--pf-radius-lg);
    overflow: hidden;
    box-shadow: var(--pf-shadow-lg);
    border: 1px solid var(--pf-gray-200);
}

.cw-mkt-code-header {
    background: var(--pf-gray-800);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cw-mkt-code-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.cw-mkt-code-title {
    margin-left: 0.5rem;
    font-size: 0.8125rem;
    color: var(--pf-gray-400);
    font-weight: 500;
}

.cw-mkt-code {
    background: var(--pf-gray-900);
    color: #e2e8f0;
    padding: 1.25rem 1.5rem;
    font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
    font-size: 0.875rem;
    line-height: 1.7;
    overflow-x: auto;
    margin: 0;
}

.cw-mkt-code .cw-c {
    color: #64748b;
}

/* ── Install grid ───────────────────────────────────────────────────── */
.cw-mkt-install-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    max-width: 640px;
    margin: 1.5rem auto 0;
}

@media (max-width: 768px) {
    .cw-mkt-install-grid { grid-template-columns: repeat(2, 1fr); }
}

.cw-mkt-install-item {
    text-align: center;
    padding: 1rem;
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-md);
}

.cw-mkt-install-item strong {
    display: block;
    font-size: 0.9375rem;
    color: var(--pf-gray-900);
}

.cw-mkt-install-item span {
    font-size: 0.8125rem;
    color: var(--pf-gray-400);
}

/* ── CTA ────────────────────────────────────────────────────────────── */
.cw-mkt-cta {
    text-align: center;
    padding: 3rem 0 2rem;
}

.cw-mkt-cta h2 {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.cw-mkt-cta p {
    color: var(--pf-gray-500);
    margin: 0.75rem 0 1.5rem;
    font-size: 1.0625rem;
}

/* ── Lightbox ───────────────────────────────────────────────────────── */
.cw-mkt-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

.cw-mkt-lightbox.active {
    display: flex;
}

.cw-mkt-lightbox-inner {
    max-width: 95vw;
    max-height: 90vh;
    position: relative;
}

.cw-mkt-lightbox-inner img {
    max-width: 100%;
    max-height: 85vh;
    border-radius: var(--pf-radius-lg);
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
}

.cw-mkt-lightbox-close {
    position: absolute;
    top: -1rem;
    right: -1rem;
    width: 2.5rem;
    height: 2.5rem;
    background: #fff;
    border: none;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--pf-shadow-md);
    color: var(--pf-gray-700);
}

.cw-mkt-lightbox-close:hover {
    background: var(--pf-gray-100);
}

.cw-mkt-lightbox-label {
    text-align: center;
    color: #fff;
    font-size: 0.9375rem;
    font-weight: 600;
    margin-top: 1rem;
}
</style>

<script>
function cwMktLightbox(url, label) {
    document.getElementById('cwMktLightboxImg').src = url;
    document.getElementById('cwMktLightboxLabel').textContent = label;
    document.getElementById('cwMktLightbox').classList.add('active');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('cwMktLightbox').classList.remove('active');
    }
});
</script>
@endsection
