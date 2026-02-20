@extends('layouts.app')
@section('title', 'Schedules')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Post Schedules</h1>
    <a href="{{ route('schedules.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">New Schedule</a>
</div>

@if($schedules->isEmpty())
    <div class="bg-white rounded-lg shadow-sm px-6 py-12 text-center text-gray-500">
        <p>No schedules yet. Schedules automatically create posts from your data files using cron timing.</p>
        <a href="{{ route('schedules.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Create your first schedule</a>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cron</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($schedules as $schedule)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $schedule->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $schedule->socialAccount?->platformLabel() }}<br>
                            <span class="text-xs">{{ $schedule->socialAccount?->username }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $schedule->cron_expression }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $schedule->dataFile?->name ?? 'None' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($schedule->dataFile)
                                {{ $schedule->current_line }}/{{ $schedule->dataFile->total_lines }} lines
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $schedule->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <form method="POST" action="{{ route('schedules.toggle', $schedule) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $schedule->is_active ? 'yellow' : 'green' }}-600 hover:underline">
                                    {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                                </button>
                            </form>
                            <a href="{{ route('schedules.edit', $schedule) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                            <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this schedule?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $schedules->links() }}
        </div>
    </div>
@endif
@endsection
