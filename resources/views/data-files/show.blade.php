@extends('layouts.app')
@section('title', 'Data File Preview')

@section('content')
<div class="pf-max-w-3xl">
    <div class="pf-page-header">
        <h1>{{ $dataFile->name }}</h1>
        <a href="{{ route('data-files.index') }}" class="pf-btn pf-btn-ghost pf-btn-sm">&larr; Back</a>
    </div>

    <div class="pf-card pf-mb-6">
        <div class="pf-card-body">
            <div class="pf-grid pf-grid-4 pf-text-sm">
                <div>
                    <p class="pf-text-muted">Original File</p>
                    <p class="pf-font-semibold">{{ $dataFile->original_filename }}</p>
                </div>
                <div>
                    <p class="pf-text-muted">Total Lines</p>
                    <p class="pf-font-semibold">{{ $dataFile->total_lines }}</p>
                </div>
                <div>
                    <p class="pf-text-muted">Lines Used</p>
                    <p class="pf-font-semibold">{{ $dataFile->used_lines }}</p>
                </div>
                <div>
                    <p class="pf-text-muted">File Size</p>
                    <p class="pf-font-semibold">{{ number_format($dataFile->file_size / 1024, 1) }} KB</p>
                </div>
            </div>
        </div>
    </div>

    <div class="pf-card">
        <div class="pf-card-header">
            <h2>Preview (first 20 lines)</h2>
        </div>
        @forelse($preview as $index => $line)
            <div class="pf-list-item" style="align-items: flex-start;">
                <span class="pf-text-xs pf-text-muted pf-font-mono" style="width: 2rem; flex-shrink: 0;">{{ $index + 1 }}</span>
                <p class="pf-text-sm {{ $index < $dataFile->used_lines ? 'pf-text-muted' : '' }}" style="{{ $index < $dataFile->used_lines ? 'text-decoration: line-through;' : '' }}">
                    {{ $line }}
                </p>
            </div>
        @empty
            <div class="pf-card-body pf-text-center pf-text-muted">File is empty.</div>
        @endforelse
        @if($dataFile->total_lines > 20)
            <div class="pf-card-footer pf-text-center pf-text-sm pf-text-muted" style="background: var(--pf-gray-50);">
                ...and {{ $dataFile->total_lines - 20 }} more lines
            </div>
        @endif
    </div>
</div>
@endsection
