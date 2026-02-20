@extends('layouts.app')
@section('title', 'Social Accounts')

@section('content')
<div class="pf-page-header">
    <h1>Social Accounts</h1>
    <a href="{{ route('social-accounts.create') }}" class="pf-btn pf-btn-primary">Connect Account</a>
</div>

@if($accounts->isEmpty())
    <div class="pf-card">
        <div class="pf-card-body pf-text-center pf-text-muted">
            <p>No social accounts connected yet.</p>
            <a href="{{ route('social-accounts.create') }}" class="pf-mt-2 pf-inline-block">Connect your first account</a>
        </div>
    </div>
@else
    <div class="pf-grid pf-grid-3">
        @foreach($accounts as $account)
            <div class="pf-card">
                <div class="pf-card-body">
                    <div class="pf-flex pf-items-center pf-justify-between pf-mb-4">
                        <div class="pf-flex pf-items-center pf-gap-3">
                            <div class="pf-platform-icon {{ $account->platform }}">
                                @if($account->platform === 'twitter')
                                    X
                                @elseif($account->platform === 'facebook')
                                    f
                                @elseif($account->platform === 'linkedin')
                                    in
                                @elseif($account->platform === 'instagram')
                                    ig
                                @endif
                            </div>
                            <h3 class="pf-font-semibold">{{ $account->platformLabel() }}</h3>
                        </div>
                        <span class="pf-badge {{ $account->is_active ? 'pf-badge-green' : 'pf-badge-gray' }}">
                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="pf-text-sm">{{ '@' . $account->username }}</p>
                    @if($account->display_name)
                        <p class="pf-text-sm pf-text-muted">{{ $account->display_name }}</p>
                    @endif
                    <p class="pf-text-xs pf-text-muted pf-mt-2">
                        {{ $account->posts()->count() }} posts &middot;
                        Connected {{ $account->created_at->diffForHumans() }}
                    </p>
                    <div class="pf-flex pf-gap-3 pf-mt-4" style="padding-top: 0.75rem; border-top: 1px solid var(--pf-gray-100);">
                        <a href="{{ route('social-accounts.edit', $account) }}" class="pf-btn pf-btn-ghost pf-btn-sm">Edit</a>
                        <form method="POST" action="{{ route('social-accounts.destroy', $account) }}" class="pf-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger" onclick="return confirm('Remove this account? Associated posts will also be deleted.')">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
