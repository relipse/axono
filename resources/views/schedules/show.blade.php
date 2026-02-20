@extends('layouts.app')
@section('title', 'Schedule Details')

@section('content')
<div class="pf-max-w-2xl">
    <div class="pf-page-header">
        <h1>{{ $schedule->name }}</h1>
        <a href="{{ route('schedules.index') }}" class="pf-btn pf-btn-ghost pf-btn-sm">&larr; Back</a>
    </div>

    <div class="pf-card">
        <div class="pf-card-body">
            <div class="pf-mb-4">
                <span class="pf-badge {{ $schedule->is_active ? 'pf-badge-green' : 'pf-badge-gray' }}">
                    {{ $schedule->is_active ? 'Active' : 'Paused' }}
                </span>
            </div>

            @if($schedule->description)
                <div class="pf-mb-4">
                    <h3 class="pf-label">Description</h3>
                    <p>{{ $schedule->description }}</p>
                </div>
            @endif

            <div class="pf-grid pf-grid-2 pf-mb-4">
                <div>
                    <h3 class="pf-label">Social Account</h3>
                    <p>{{ $schedule->socialAccount?->platformLabel() }} - {{ $schedule->socialAccount?->username }}</p>
                </div>
                <div>
                    <h3 class="pf-label">Data File</h3>
                    <p>{{ $schedule->dataFile?->name ?? 'None' }}</p>
                </div>
                <div>
                    <h3 class="pf-label">Cron Expression</h3>
                    <p class="pf-font-mono">{{ $schedule->cron_expression }}</p>
                </div>
                <div>
                    <h3 class="pf-label">Timezone</h3>
                    <p>{{ $schedule->timezone }}</p>
                </div>
                <div>
                    <h3 class="pf-label">Posts Per Run</h3>
                    <p>{{ $schedule->posts_per_run }}</p>
                </div>
                <div>
                    <h3 class="pf-label">Progress</h3>
                    <p>{{ $schedule->current_line }} / {{ $schedule->dataFile?->total_lines ?? 0 }} lines</p>
                </div>
            </div>

            @if($schedule->last_run_at || $schedule->next_run_at)
                <div class="pf-grid pf-grid-2 pf-mt-4" style="padding-top: 1rem; border-top: 1px solid var(--pf-gray-200);">
                    @if($schedule->last_run_at)
                        <div>
                            <h3 class="pf-label">Last Run</h3>
                            <p>{{ $schedule->last_run_at->format('M j, Y g:i A') }}</p>
                        </div>
                    @endif
                    @if($schedule->next_run_at)
                        <div>
                            <h3 class="pf-label">Next Run</h3>
                            <p>{{ $schedule->next_run_at->format('M j, Y g:i A') }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="pf-flex pf-gap-3 pf-mt-6" style="padding-top: 1rem; border-top: 1px solid var(--pf-gray-200);">
                <a href="{{ route('schedules.edit', $schedule) }}" class="pf-btn pf-btn-primary">Edit</a>
                <form method="POST" action="{{ route('schedules.toggle', $schedule) }}">
                    @csrf
                    <button type="submit" class="pf-btn pf-btn-secondary">
                        {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
