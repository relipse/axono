@extends('layouts.claude-worker')
@section('title', 'Admin Login')

@section('content')
<div style="max-width: 24rem; margin: 4rem auto;">
    <div class="pf-card">
        <div class="pf-card-header">
            <h3>Admin Login</h3>
        </div>
        <div class="pf-card-body">
            <form method="POST" action="{{ route('claude-worker.login') }}">
                @csrf
                <div class="pf-form-group">
                    <label class="pf-label">Password</label>
                    <input type="password" name="password" class="pf-input" placeholder="Enter admin password" required autofocus>
                    <p class="pf-hint">Set via CLAUDE_WORKER_PASSWORD in .env</p>
                </div>
                <button type="submit" class="pf-btn pf-btn-primary pf-w-full">Login</button>
            </form>
        </div>
    </div>
</div>
@endsection
