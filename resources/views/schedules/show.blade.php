@extends('layouts.app')
@section('title', 'Schedule Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $schedule->name }}</h1>
        <a href="{{ route('schedules.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">&larr; Back</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
        <div class="flex items-center space-x-2">
            <span class="px-2 py-1 text-xs rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $schedule->is_active ? 'Active' : 'Paused' }}
            </span>
        </div>

        @if($schedule->description)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                <p class="text-gray-900">{{ $schedule->description }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Social Account</h3>
                <p class="text-gray-900">{{ $schedule->socialAccount?->platformLabel() }} - {{ $schedule->socialAccount?->username }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Data File</h3>
                <p class="text-gray-900">{{ $schedule->dataFile?->name ?? 'None' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Cron Expression</h3>
                <p class="text-gray-900 font-mono">{{ $schedule->cron_expression }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Timezone</h3>
                <p class="text-gray-900">{{ $schedule->timezone }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Posts Per Run</h3>
                <p class="text-gray-900">{{ $schedule->posts_per_run }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Progress</h3>
                <p class="text-gray-900">{{ $schedule->current_line }} / {{ $schedule->dataFile?->total_lines ?? 0 }} lines</p>
            </div>
        </div>

        @if($schedule->last_run_at || $schedule->next_run_at)
            <div class="border-t border-gray-200 pt-4 grid grid-cols-2 gap-4">
                @if($schedule->last_run_at)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Last Run</h3>
                        <p class="text-gray-900">{{ $schedule->last_run_at->format('M j, Y g:i A') }}</p>
                    </div>
                @endif
                @if($schedule->next_run_at)
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Next Run</h3>
                        <p class="text-gray-900">{{ $schedule->next_run_at->format('M j, Y g:i A') }}</p>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex space-x-3 pt-4 border-t border-gray-200">
            <a href="{{ route('schedules.edit', $schedule) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Edit</a>
            <form method="POST" action="{{ route('schedules.toggle', $schedule) }}">
                @csrf
                <button type="submit" class="px-4 py-2 {{ $schedule->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-md text-sm">
                    {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
