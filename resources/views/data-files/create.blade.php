@extends('layouts.app')
@section('title', 'Upload Data File')

@section('content')
<div class="pf-max-w-2xl">
    <h1 class="pf-text-2xl pf-font-bold pf-mb-6">Upload Data File</h1>

    <div class="pf-alert pf-alert-info pf-mb-6">
        <p class="pf-font-semibold pf-mb-2">File Format:</p>
        <ul style="margin-left: 1.25rem; list-style: disc;">
            <li>Plain text file (.txt) or CSV file (.csv)</li>
            <li>Each line will become a separate social media post</li>
            <li>Empty lines will be skipped</li>
            <li>Max file size: {{ auth()->user()->subscriptionPlan()?->max_file_size_mb ?? 1 }}MB</li>
        </ul>
    </div>

    <div class="pf-card">
        <div class="pf-card-body">
            <form method="POST" action="{{ route('data-files.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="pf-form-group">
                    <label for="name" class="pf-label">File Name / Label</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="pf-input"
                        placeholder="e.g., Marketing Quotes Q1">
                </div>

                <div class="pf-form-group">
                    <label for="file" class="pf-label">Select File</label>
                    <input id="file" type="file" name="file" required accept=".txt,.csv"
                        class="pf-file-input">
                </div>

                <div class="pf-flex pf-gap-3" style="justify-content: flex-end;">
                    <a href="{{ route('data-files.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                    <button type="submit" class="pf-btn pf-btn-primary">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
