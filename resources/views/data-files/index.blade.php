@extends('layouts.app')
@section('title', 'Data Files')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Data Files</h1>
    <a href="{{ route('data-files.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Upload File</a>
</div>

<div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md mb-6 text-sm">
    Upload text files (.txt or .csv) where each line represents a post. These files can be assigned to schedules for automatic posting.
</div>

@if($files->isEmpty())
    <div class="bg-white rounded-lg shadow-sm px-6 py-12 text-center text-gray-500">
        <p>No data files uploaded yet.</p>
        <a href="{{ route('data-files.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Upload your first file</a>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Original File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lines</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Uploaded</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($files as $file)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $file->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $file->original_filename }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $file->total_lines }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $file->used_lines }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ number_format($file->file_size / 1024, 1) }} KB</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $file->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <a href="{{ route('data-files.show', $file) }}" class="text-indigo-600 hover:text-indigo-800">Preview</a>
                            <form method="POST" action="{{ route('data-files.destroy', $file) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this file?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $files->links() }}
        </div>
    </div>
@endif
@endsection
