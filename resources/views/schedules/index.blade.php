@extends('layouts.app')
@section('title', 'Schedules')

@section('content')
<div class="pf-page-header">
    <h1>Post Schedules</h1>
    <a href="{{ route('schedules.create') }}" class="pf-btn pf-btn-primary">New Schedule</a>
</div>

@if($schedules->isEmpty())
    <div class="pf-card">
        <div class="pf-card-body pf-text-center pf-text-muted">
            <p>No schedules yet. Schedules automatically create posts from your data files using cron timing.</p>
            <a href="{{ route('schedules.create') }}" class="pf-mt-2 pf-inline-block">Create your first schedule</a>
        </div>
    </div>
@else
    <div class="pf-card">
        <table class="pf-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Account</th>
                    <th>Cron</th>
                    <th>Data File</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                    <tr>
                        <td class="pf-font-semibold">{{ $schedule->name }}</td>
                        <td>
                            {{ $schedule->socialAccount?->platformLabel() }}<br>
                            <span class="pf-text-xs">{{ $schedule->socialAccount?->username }}</span>
                        </td>
                        <td class="pf-font-mono">{{ $schedule->cron_expression }}</td>
                        <td>{{ $schedule->dataFile?->name ?? 'None' }}</td>
                        <td>
                            @if($schedule->dataFile)
                                {{ $schedule->current_line }}/{{ $schedule->dataFile->total_lines }} lines
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="pf-badge {{ $schedule->is_active ? 'pf-badge-green' : 'pf-badge-gray' }}">
                                {{ $schedule->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <form method="POST" action="{{ route('schedules.toggle', $schedule) }}" class="pf-inline">
                                @csrf
                                <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm">
                                    {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                                </button>
                            </form>
                            <a href="{{ route('schedules.edit', $schedule) }}" class="pf-btn pf-btn-ghost pf-btn-sm">Edit</a>
                            <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="pf-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger" onclick="return confirm('Delete this schedule?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pf-pagination">
            {{ $schedules->links() }}
        </div>
    </div>
@endif
@endsection
