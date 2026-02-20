@extends('layouts.app')
@section('title', 'Edit Social Account')

@section('content')
<div class="pf-max-w-2xl">
    <h1 class="pf-text-2xl pf-font-bold pf-mb-6">Edit Social Account</h1>

    <div class="pf-card">
        <div class="pf-card-body">
            <form method="POST" action="{{ route('social-accounts.update', $socialAccount) }}">
                @csrf
                @method('PUT')

                <div class="pf-form-group">
                    <label class="pf-label">Platform</label>
                    <p>{{ $socialAccount->platformLabel() }}</p>
                </div>

                <div class="pf-form-group">
                    <label class="pf-label">Username</label>
                    <p>{{ '@' . $socialAccount->username }}</p>
                    <p class="pf-hint">Username is set automatically via OAuth</p>
                </div>

                <div class="pf-form-group">
                    <label for="display_name" class="pf-label">Display Name</label>
                    <input id="display_name" type="text" name="display_name" value="{{ old('display_name', $socialAccount->display_name) }}"
                        class="pf-input">
                </div>

                <div class="pf-form-group">
                    <label class="pf-label">Token Status</label>
                    @if($socialAccount->isTokenExpired())
                        <p class="pf-text-sm pf-text-danger">Token expired. Please reconnect below.</p>
                    @elseif($socialAccount->token_expires_at)
                        <p class="pf-text-sm" style="color: var(--pf-success-700);">Valid until {{ $socialAccount->token_expires_at->format('M j, Y g:i A') }}</p>
                    @else
                        <p class="pf-text-sm" style="color: var(--pf-success-700);">Active (no expiration)</p>
                    @endif
                </div>

                <div class="pf-form-group">
                    <div class="pf-checkbox-group">
                        <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $socialAccount->is_active) ? 'checked' : '' }}
                            class="pf-checkbox">
                        <label for="is_active" class="pf-text-sm">Active</label>
                    </div>
                </div>

                <div class="pf-flex pf-items-center pf-justify-between pf-mt-4" style="padding-top: 1rem; border-top: 1px solid var(--pf-gray-200);">
                    <a href="{{ route('social.redirect', $socialAccount->platform) }}" class="pf-text-sm pf-font-semibold">
                        Reconnect / Refresh Tokens
                    </a>
                    <div class="pf-flex pf-gap-3">
                        <a href="{{ route('social-accounts.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                        <button type="submit" class="pf-btn pf-btn-primary">Update Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
