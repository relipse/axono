@extends('layouts.app')
@section('title', 'Data Files')

@section('content')
<div class="pf-page-header">
    <h1>Data Files</h1>
    <a href="{{ route('data-files.create') }}" class="pf-btn pf-btn-primary">Upload File</a>
</div>

<div class="pf-alert pf-alert-info pf-mb-6">
    Upload text files (.txt or .csv) where each line represents a post. These files can be assigned to schedules for automatic posting.
</div>

@if($files->isEmpty())
    <div class="pf-card">
        <div class="pf-card-body pf-text-center pf-text-muted">
            <p>No data files uploaded yet.</p>
            <a href="{{ route('data-files.create') }}" class="pf-mt-2 pf-inline-block">Upload your first file</a>
        </div>
    </div>
@else
    <div class="pf-card">
        <table class="pf-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Original File</th>
                    <th>Lines</th>
                    <th>Used</th>
                    <th>Size</th>
                    <th>Uploaded</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($files as $file)
                    <tr>
                        <td class="pf-font-semibold">{{ $file->name }}</td>
                        <td>{{ $file->original_filename }}</td>
                        <td>{{ $file->total_lines }}</td>
                        <td>{{ $file->used_lines }}</td>
                        <td>{{ number_format($file->file_size / 1024, 1) }} KB</td>
                        <td>{{ $file->created_at->diffForHumans() }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('data-files.show', $file) }}" class="pf-btn pf-btn-ghost pf-btn-sm">Preview</a>
                            <form method="POST" action="{{ route('data-files.destroy', $file) }}" class="pf-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pf-btn pf-btn-ghost pf-btn-sm pf-text-danger" onclick="return confirm('Delete this file?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pf-pagination">
            {{ $files->links() }}
        </div>
    </div>
@endif
@endsection
