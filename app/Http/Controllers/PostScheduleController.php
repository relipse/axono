<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PostSchedule;
use Illuminate\Http\Request;

class PostScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = $request->user()->postSchedules()
            ->with(['socialAccount', 'dataFile'])
            ->latest()
            ->paginate(20);

        return view('schedules.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        $accounts = $request->user()->socialAccounts()->where('is_active', true)->get();
        $dataFiles = $request->user()->dataFiles()->latest()->get();
        return view('schedules.create', compact('accounts', 'dataFiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'social_account_id' => ['required', 'exists:social_accounts,id'],
            'data_file_id' => ['required', 'exists:data_files,id'],
            'cron_expression' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'posts_per_run' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        // Verify ownership
        $request->user()->socialAccounts()->findOrFail($validated['social_account_id']);
        $request->user()->dataFiles()->findOrFail($validated['data_file_id']);

        // Validate cron expression
        try {
            new \Cron\CronExpression($validated['cron_expression']);
        } catch (\Exception $e) {
            return back()->withErrors(['cron_expression' => 'Invalid cron expression.'])->withInput();
        }

        $schedule = $request->user()->postSchedules()->create($validated);
        $schedule->calculateNextRun();

        ActivityLog::log($request->user(), 'schedule.created', $schedule);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function show(PostSchedule $schedule)
    {
        $this->authorize('view', $schedule);
        $schedule->load(['socialAccount', 'dataFile']);
        return view('schedules.show', compact('schedule'));
    }

    public function edit(PostSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $accounts = $schedule->user->socialAccounts()->where('is_active', true)->get();
        $dataFiles = $schedule->user->dataFiles()->latest()->get();

        return view('schedules.edit', compact('schedule', 'accounts', 'dataFiles'));
    }

    public function update(Request $request, PostSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'social_account_id' => ['required', 'exists:social_accounts,id'],
            'data_file_id' => ['required', 'exists:data_files,id'],
            'cron_expression' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'posts_per_run' => ['required', 'integer', 'min:1', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $request->user()->socialAccounts()->findOrFail($validated['social_account_id']);
        $request->user()->dataFiles()->findOrFail($validated['data_file_id']);

        try {
            new \Cron\CronExpression($validated['cron_expression']);
        } catch (\Exception $e) {
            return back()->withErrors(['cron_expression' => 'Invalid cron expression.'])->withInput();
        }

        $schedule->update($validated);
        $schedule->calculateNextRun();

        ActivityLog::log($request->user(), 'schedule.updated', $schedule);

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule updated.');
    }

    public function destroy(Request $request, PostSchedule $schedule)
    {
        $this->authorize('delete', $schedule);

        ActivityLog::log($request->user(), 'schedule.deleted', $schedule);

        $schedule->delete();

        return redirect()->route('schedules.index')
            ->with('success', 'Schedule deleted.');
    }

    public function toggle(Request $request, PostSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $schedule->update(['is_active' => !$schedule->is_active]);

        $action = $schedule->is_active ? 'activated' : 'paused';
        ActivityLog::log($request->user(), "schedule.{$action}", $schedule);

        return redirect()->route('schedules.index')
            ->with('success', "Schedule {$action}.");
    }
}
