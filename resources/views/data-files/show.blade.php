@extends('layouts.app')
@section('title', 'Data File Preview')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $dataFile->name }}</h1>
        <a href="{{ route('data-files.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">&larr; Back</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Original File</p>
                <p class="font-medium">{{ $dataFile->original_filename }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Lines</p>
                <p class="font-medium">{{ $dataFile->total_lines }}</p>
            </div>
            <div>
                <p class="text-gray-500">Lines Used</p>
                <p class="font-medium">{{ $dataFile->used_lines }}</p>
            </div>
            <div>
                <p class="text-gray-500">File Size</p>
                <p class="font-medium">{{ number_format($dataFile->file_size / 1024, 1) }} KB</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Preview (first 20 lines)</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($preview as $index => $line)
                <div class="px-6 py-3 flex items-start">
                    <span class="text-gray-400 text-xs font-mono w-8 flex-shrink-0 pt-0.5">{{ $index + 1 }}</span>
                    <p class="text-sm text-gray-700 {{ $index < $dataFile->used_lines ? 'line-through text-gray-400' : '' }}">
                        {{ $line }}
                    </p>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">File is empty.</div>
            @endforelse
        </div>
        @if($dataFile->total_lines > 20)
            <div class="px-6 py-3 bg-gray-50 text-sm text-gray-500 text-center">
                ...and {{ $dataFile->total_lines - 20 }} more lines
            </div>
        @endif
    </div>
</div>
@endsection
