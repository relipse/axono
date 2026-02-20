@extends('layouts.app')
@section('title', 'Create Schedule')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Post Schedule</h1>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md mb-6 text-sm">
        A schedule reads lines from a data file and creates posts automatically based on the cron expression.
        Each line in the data file becomes a separate post.
    </div>

    <form method="POST" action="{{ route('schedules.store') }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Schedule Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                placeholder="e.g., Daily Motivation Tweets">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
            <textarea id="description" name="description" rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="social_account_id" class="block text-sm font-medium text-gray-700">Social Account</label>
            <select id="social_account_id" name="social_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                <option value="">Select an account...</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('social_account_id') == $account->id ? 'selected' : '' }}>
                        {{ $account->platformLabel() }} - {{ $account->username }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="data_file_id" class="block text-sm font-medium text-gray-700">Data File</label>
            <select id="data_file_id" name="data_file_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                <option value="">Select a data file...</option>
                @foreach($dataFiles as $file)
                    <option value="{{ $file->id }}" {{ old('data_file_id') == $file->id ? 'selected' : '' }}>
                        {{ $file->name }} ({{ $file->total_lines }} lines)
                    </option>
                @endforeach
            </select>
            @if($dataFiles->isEmpty())
                <p class="text-sm text-yellow-600 mt-1">No data files uploaded. <a href="{{ route('data-files.create') }}" class="underline">Upload one first</a>.</p>
            @endif
        </div>

        <div>
            <label for="cron_expression" class="block text-sm font-medium text-gray-700">Cron Expression</label>
            <input id="cron_expression" type="text" name="cron_expression" value="{{ old('cron_expression', '0 9 * * *') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border font-mono"
                placeholder="0 9 * * *">
            <p class="text-xs text-gray-400 mt-1">
                Examples: <code>0 9 * * *</code> (daily at 9am), <code>0 9,15 * * *</code> (9am and 3pm),
                <code>0 */4 * * *</code> (every 4 hours), <code>0 9 * * 1-5</code> (weekdays at 9am)
            </p>
        </div>

        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
            <select id="timezone" name="timezone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney'] as $tz)
                    <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="posts_per_run" class="block text-sm font-medium text-gray-700">Posts Per Run</label>
            <input id="posts_per_run" type="number" name="posts_per_run" value="{{ old('posts_per_run', 1) }}" required min="1" max="50"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
            <p class="text-xs text-gray-400 mt-1">Number of lines to read from the data file on each run</p>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('schedules.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Create Schedule</button>
        </div>
    </form>
</div>
@endsection
