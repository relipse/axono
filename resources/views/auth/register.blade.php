@extends('layouts.guest')
@section('title', 'Register')
@section('heading', 'Create your account')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="pf-form-group">
        <label for="name" class="pf-label">Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <label for="email" class="pf-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <label for="password" class="pf-label">Password</label>
        <input id="password" type="password" name="password" required
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <label for="password_confirmation" class="pf-label">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
            class="pf-input">
    </div>

    <div class="pf-form-group">
        <button type="submit" class="pf-btn pf-btn-primary pf-w-full">
            Register
        </button>
    </div>

    <p class="pf-text-center pf-text-sm pf-text-muted">
        Already have an account? <a href="{{ route('login') }}">Login</a>
    </p>
</form>
@endsection
