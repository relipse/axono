@extends('layouts.app')
@section('title', 'Edit Schedule')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Schedule</h1>

    <form method="POST" action="{{ route('schedules.update', $schedule) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Schedule Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $schedule->name) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">{{ old('description', $schedule->description) }}</textarea>
        </div>

        <div>
            <label for="social_account_id" class="block text-sm font-medium text-gray-700">Social Account</label>
            <select id="social_account_id" name="social_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('social_account_id', $schedule->social_account_id) == $account->id ? 'selected' : '' }}>
                        {{ $account->platformLabel() }} - {{ $account->username }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="data_file_id" class="block text-sm font-medium text-gray-700">Data File</label>
            <select id="data_file_id" name="data_file_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                @foreach($dataFiles as $file)
                    <option value="{{ $file->id }}" {{ old('data_file_id', $schedule->data_file_id) == $file->id ? 'selected' : '' }}>
                        {{ $file->name }} ({{ $file->total_lines }} lines)
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="cron_expression" class="block text-sm font-medium text-gray-700">Cron Expression</label>
            <input id="cron_expression" type="text" name="cron_expression" value="{{ old('cron_expression', $schedule->cron_expression) }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border font-mono">
        </div>

        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
            <select id="timezone" name="timezone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
                @foreach(['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris', 'Asia/Tokyo', 'Asia/Shanghai', 'Australia/Sydney'] as $tz)
                    <option value="{{ $tz }}" {{ old('timezone', $schedule->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="posts_per_run" class="block text-sm font-medium text-gray-700">Posts Per Run</label>
            <input id="posts_per_run" type="number" name="posts_per_run" value="{{ old('posts_per_run', $schedule->posts_per_run) }}" required min="1" max="50"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">
        </div>

        <div class="flex items-center">
            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
        </div>

        <div class="bg-gray-50 rounded-md p-4 text-sm text-gray-600">
            <p><strong>Progress:</strong> {{ $schedule->current_line }} / {{ $schedule->dataFile?->total_lines ?? 0 }} lines processed</p>
            @if($schedule->last_run_at)
                <p><strong>Last Run:</strong> {{ $schedule->last_run_at->format('M j, Y g:i A') }}</p>
            @endif
            @if($schedule->next_run_at)
                <p><strong>Next Run:</strong> {{ $schedule->next_run_at->format('M j, Y g:i A') }}</p>
            @endif
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('schedules.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Schedule</button>
        </div>
    </form>
</div>
@endsection
