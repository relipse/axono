@extends('layouts.app')
@section('title', 'Connect Social Account')

@section('content')
<div class="pf-max-w-2xl">
    <h1 class="pf-text-2xl pf-font-bold pf-mb-6">Connect Social Account</h1>

    <p class="pf-text-muted pf-mb-6">Click a platform below to authorize via OAuth. You'll be redirected to the platform to grant access, then returned here automatically.</p>

    <div class="pf-flex pf-flex-col pf-gap-4">
        {{-- Twitter / X --}}
        <div class="pf-card">
            <div class="pf-card-body pf-flex pf-items-center pf-justify-between">
                <div class="pf-flex pf-items-center pf-gap-4">
                    <div class="pf-platform-icon twitter">X</div>
                    <div>
                        <h3 class="pf-font-semibold">Twitter / X</h3>
                        <p class="pf-text-sm pf-text-muted">Post tweets on your behalf</p>
                    </div>
                </div>
                @if(in_array('twitter', $connectedPlatforms))
                    <span class="pf-badge pf-badge-green">Connected</span>
                @else
                    <a href="{{ route('social.redirect', 'twitter') }}"
                       class="pf-btn pf-btn-twitter">
                        Connect
                    </a>
                @endif
            </div>
        </div>

        {{-- Facebook --}}
        <div class="pf-card">
            <div class="pf-card-body pf-flex pf-items-center pf-justify-between">
                <div class="pf-flex pf-items-center pf-gap-4">
                    <div class="pf-platform-icon facebook">f</div>
                    <div>
                        <h3 class="pf-font-semibold">Facebook</h3>
                        <p class="pf-text-sm pf-text-muted">Post to your pages and profile</p>
                    </div>
                </div>
                @if(in_array('facebook', $connectedPlatforms))
                    <span class="pf-badge pf-badge-green">Connected</span>
                @else
                    <a href="{{ route('social.redirect', 'facebook') }}"
                       class="pf-btn pf-btn-facebook">
                        Connect
                    </a>
                @endif
            </div>
        </div>

        {{-- LinkedIn --}}
        <div class="pf-card">
            <div class="pf-card-body pf-flex pf-items-center pf-justify-between">
                <div class="pf-flex pf-items-center pf-gap-4">
                    <div class="pf-platform-icon linkedin">in</div>
                    <div>
                        <h3 class="pf-font-semibold">LinkedIn</h3>
                        <p class="pf-text-sm pf-text-muted">Share professional updates</p>
                    </div>
                </div>
                @if(in_array('linkedin', $connectedPlatforms))
                    <span class="pf-badge pf-badge-green">Connected</span>
                @else
                    <a href="{{ route('social.redirect', 'linkedin') }}"
                       class="pf-btn pf-btn-linkedin">
                        Connect
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="pf-mt-6">
        <a href="{{ route('social-accounts.index') }}" class="pf-btn pf-btn-ghost pf-btn-sm">&larr; Back to Accounts</a>
    </div>
</div>
@endsection
