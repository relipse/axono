@extends('layouts.app')
@section('title', 'Create Schedule')

@section('content')
<div class="pf-max-w-2xl">
    <h1 class="pf-text-2xl pf-font-bold pf-mb-6">Create Post Schedule</h1>

    <div class="pf-alert pf-alert-info pf-mb-6">
        A schedule reads lines from a data file and creates posts automatically based on the cron expression.
        Each line in the data file becomes a separate post.
    </div>

    <div class="pf-card">
        <div class="pf-card-body">
            <form method="POST" action="{{ route('schedules.store') }}">
                @csrf

                <div class="pf-form-group">
                    <label for="name" class="pf-label">Schedule Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="pf-input"
                        placeholder="e.g., Daily Motivation Tweets">
                </div>

                <div class="pf-form-group">
                    <label for="description" class="pf-label">Description (optional)</label>
                    <textarea id="description" name="description" rows="2"
                        class="pf-textarea">{{ old('description') }}</textarea>
                </div>

                <div class="pf-form-group">
                    <label for="social_account_id" class="pf-label">Social Account</label>
                    <select id="social_account_id" name="social_account_id" required class="pf-select">
                        <option value="">Select an account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('social_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->platformLabel() }} - {{ $account->username }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-form-group">
                    <label for="data_file_id" class="pf-label">Data File</label>
                    <select id="data_file_id" name="data_file_id" required class="pf-select">
                        <option value="">Select a data file...</option>
                        @foreach($dataFiles as $file)
                            <option value="{{ $file->id }}" {{ old('data_file_id') == $file->id ? 'selected' : '' }}>
                                {{ $file->name }} ({{ $file->total_lines }} lines)
                            </option>
                        @endforeach
                    </select>
                    @if($dataFiles->isEmpty())
                        <p class="pf-hint" style="color: var(--pf-warning-700);">No data files uploaded. <a href="{{ route('data-files.create') }}" style="text-decoration: underline;">Upload one first</a>.</p>
                    @endif
                </div>

                <div class="pf-form-group">
                    <label for="cron_expression" class="pf-label">Cron Expression</label>
                    <input id="cron_expression" type="text" name="cron_expression" value="{{ old('cron_expression', '0 9 * * *') }}" required
                        class="pf-input pf-font-mono"
                        placeholder="0 9 * * *">
                    <p class="pf-hint">
                        Examples: <code>0 9 * * *</code> (daily at 9am), <code>0 9,15 * * *</code> (9am and 3pm),
                        <code>0 */4 * * *</code> (every 4 hours), <code>0 9 * * 1-5</code> (weekdays at 9am)
                    </p>
                </div>

                <div class="pf-form-group">
                    <label for="timezone" class="pf-label">Timezone</label>
                    <select id="timezone" name="timezone" required class="pf-select">
                        @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney'] as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-form-group">
                    <label for="posts_per_run" class="pf-label">Posts Per Run</label>
                    <input id="posts_per_run" type="number" name="posts_per_run" value="{{ old('posts_per_run', 1) }}" required min="1" max="50"
                        class="pf-input">
                    <p class="pf-hint">Number of lines to read from the data file on each run</p>
                </div>

                <div class="pf-flex pf-gap-3" style="justify-content: flex-end;">
                    <a href="{{ route('schedules.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                    <button type="submit" class="pf-btn pf-btn-primary">Create Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
