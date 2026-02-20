@extends('layouts.app')
@section('title', 'Upload Data File')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Upload Data File</h1>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-md mb-6 text-sm">
        <p class="font-semibold mb-1">File Format:</p>
        <ul class="list-disc list-inside space-y-1">
            <li>Plain text file (.txt) or CSV file (.csv)</li>
            <li>Each line will become a separate social media post</li>
            <li>Empty lines will be skipped</li>
            <li>Max file size: {{ auth()->user()->subscriptionPlan()?->max_file_size_mb ?? 1 }}MB</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('data-files.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">File Name / Label</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                placeholder="e.g., Marketing Quotes Q1">
        </div>

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700">Select File</label>
            <input id="file" type="file" name="file" required accept=".txt,.csv"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('data-files.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Upload File</button>
        </div>
    </form>
</div>
@endsection
