@extends('layouts.app')
@section('title', 'Edit Schedule')

@section('content')
<div class="pf-max-w-2xl">
    <h1 class="pf-text-2xl pf-font-bold pf-mb-6">Edit Schedule</h1>

    <div class="pf-card">
        <div class="pf-card-body">
            <form method="POST" action="{{ route('schedules.update', $schedule) }}">
                @csrf
                @method('PUT')

                <div class="pf-form-group">
                    <label for="name" class="pf-label">Schedule Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $schedule->name) }}" required
                        class="pf-input">
                </div>

                <div class="pf-form-group">
                    <label for="description" class="pf-label">Description</label>
                    <textarea id="description" name="description" rows="2"
                        class="pf-textarea">{{ old('description', $schedule->description) }}</textarea>
                </div>

                <div class="pf-form-group">
                    <label for="social_account_id" class="pf-label">Social Account</label>
                    <select id="social_account_id" name="social_account_id" required class="pf-select">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('social_account_id', $schedule->social_account_id) == $account->id ? 'selected' : '' }}>
                                {{ $account->platformLabel() }} - {{ $account->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-form-group">
                    <label for="data_file_id" class="pf-label">Data File</label>
                    <select id="data_file_id" name="data_file_id" required class="pf-select">
                        @foreach($dataFiles as $file)
                            <option value="{{ $file->id }}" {{ old('data_file_id', $schedule->data_file_id) == $file->id ? 'selected' : '' }}>
                                {{ $file->name }} ({{ $file->total_lines }} lines)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-form-group">
                    <label for="cron_expression" class="pf-label">Cron Expression</label>
                    <input id="cron_expression" type="text" name="cron_expression" value="{{ old('cron_expression', $schedule->cron_expression) }}" required
                        class="pf-input pf-font-mono">
                </div>

                <div class="pf-form-group">
                    <label for="timezone" class="pf-label">Timezone</label>
                    <select id="timezone" name="timezone" required class="pf-select">
                        @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney'] as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', $schedule->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-form-group">
                    <label for="posts_per_run" class="pf-label">Posts Per Run</label>
                    <input id="posts_per_run" type="number" name="posts_per_run" value="{{ old('posts_per_run', $schedule->posts_per_run) }}" required min="1" max="50"
                        class="pf-input">
                </div>

                <div class="pf-form-group">
                    <div class="pf-checkbox-group">
                        <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}
                            class="pf-checkbox">
                        <label for="is_active" class="pf-text-sm">Active</label>
                    </div>
                </div>

                <div class="pf-alert pf-alert-info">
                    <p><strong>Progress:</strong> {{ $schedule->current_line }} / {{ $schedule->dataFile?->total_lines ?? 0 }} lines processed</p>
                    @if($schedule->last_run_at)
                        <p><strong>Last Run:</strong> {{ $schedule->last_run_at->format('M j, Y g:i A') }}</p>
                    @endif
                    @if($schedule->next_run_at)
                        <p><strong>Next Run:</strong> {{ $schedule->next_run_at->format('M j, Y g:i A') }}</p>
                    @endif
                </div>

                <div class="pf-flex pf-gap-3 pf-mt-4" style="justify-content: flex-end;">
                    <a href="{{ route('schedules.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                    <button type="submit" class="pf-btn pf-btn-primary">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
