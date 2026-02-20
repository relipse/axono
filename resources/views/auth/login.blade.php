@extends('layouts.guest')
@section('title', 'Login')
@section('heading', 'Sign in to your account')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="pf-form-group">
        <label for="email" class="pf-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <label for="password" class="pf-label">Password</label>
        <input id="password" type="password" name="password" required
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <div class="pf-checkbox-group">
            <input id="remember" type="checkbox" name="remember" class="pf-checkbox">
            <label for="remember" class="pf-text-sm">Remember me</label>
        </div>
    </div>

    <div class="pf-form-group">
        <button type="submit" class="pf-btn pf-btn-primary pf-w-full">
            Sign In
        </button>
    </div>

    <p class="pf-text-center pf-text-sm pf-text-muted">
        Don't have an account? <a href="{{ route('register') }}">Register</a>
    </p>
</form>
@endsection
