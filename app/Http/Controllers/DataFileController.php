<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DataFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DataFileController extends Controller
{
    public function index(Request $request)
    {
        $files = $request->user()->dataFiles()->latest()->paginate(20);
        return view('data-files.index', compact('files'));
    }

    public function create()
    {
        return view('data-files.create');
    }

    public function store(Request $request)
    {
        $plan = $request->user()->subscriptionPlan();
        $maxSize = $plan ? $plan->max_file_size_mb * 1024 : 1024;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:txt,csv', "max:{$maxSize}"],
        ]);

        $file = $request->file('file');
        $path = $file->store('data-files', 'local');

        $content = Storage::get($path);
        $lines = array_filter(explode("\n", $content), fn($l) => trim($l) !== '');

        $dataFile = DataFile::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'total_lines' => count($lines),
        ]);

        ActivityLog::log($request->user(), 'data_file.uploaded', $dataFile);

        return redirect()->route('data-files.index')
            ->with('success', 'Data file uploaded. ' . count($lines) . ' lines found.');
    }

    public function show(DataFile $dataFile)
    {
        $this->authorize('view', $dataFile);
        $lines = $dataFile->getLines();
        $preview = array_slice($lines, 0, 20);
        return view('data-files.show', compact('dataFile', 'preview'));
    }

    public function destroy(Request $request, DataFile $dataFile)
    {
        $this->authorize('delete', $dataFile);

        Storage::delete($dataFile->file_path);

        ActivityLog::log($request->user(), 'data_file.deleted', $dataFile);

        $dataFile->delete();

        return redirect()->route('data-files.index')
            ->with('success', 'Data file deleted.');
    }
}
