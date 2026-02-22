<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ClaudeWorkerController extends Controller
{
    /**
     * Path to the claude-worker directory (relative to project root).
     */
    protected function workerDir(): string
    {
        return base_path('claude-worker');
    }

    protected function workerScript(): string
    {
        return $this->workerDir() . '/claude-worker';
    }

    protected function outputDir(): string
    {
        return $this->workerDir() . '/output';
    }

    // ── Main admin page ─────────────────────────────────────────────────────

    public function index()
    {
        return view('claude-worker.index');
    }

    // ── Launch a new task ───────────────────────────────────────────────────

    public function launch(Request $request)
    {
        $request->validate([
            'task' => 'required|string|min:3',
            'api_key' => 'required|string|min:10',
            'repo_source' => 'required|in:url,local',
            'repo_url' => 'required_if:repo_source,url|nullable|string',
            'local_repo' => 'required_if:repo_source,local|nullable|string',
        ]);

        $cmd = [$this->workerScript()];

        if ($request->repo_source === 'local') {
            $cmd[] = '--local-repo';
            $cmd[] = $request->local_repo;
        } else {
            $cmd[] = '--repo';
            $cmd[] = $request->repo_url;
        }

        $cmd[] = '--task';
        $cmd[] = $request->task;
        $cmd[] = '--no-review';

        foreach ([
            'branch' => '--branch',
            'repo_branch' => '--repo-branch',
            'model' => '--model',
            'max_turns' => '--max-turns',
        ] as $field => $flag) {
            if ($request->filled($field)) {
                $cmd[] = $flag;
                $cmd[] = $request->input($field);
            }
        }

        if ($request->boolean('push')) {
            $cmd[] = '--push';
        }
        if ($request->boolean('rebuild')) {
            $cmd[] = '--rebuild';
        }
        if ($request->boolean('verbose')) {
            $cmd[] = '--verbose';
        }

        // Build the shell command string with proper escaping
        $shellCmd = implode(' ', array_map('escapeshellarg', $cmd));

        // Set the API key in the environment and run in background
        $taskId = 'web-' . time() . '-' . Str::random(6);
        $logDir = $this->outputDir() . '/web-tasks';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/' . $taskId . '.log';
        $pidFile = $logDir . '/' . $taskId . '.pid';
        $metaFile = $logDir . '/' . $taskId . '.json';

        // Save task metadata
        file_put_contents($metaFile, json_encode([
            'task_id' => $taskId,
            'task' => $request->task,
            'repo' => $request->repo_source === 'local' ? $request->local_repo : $request->repo_url,
            'branch' => $request->input('branch', ''),
            'model' => $request->input('model', ''),
            'started_at' => now()->toIso8601String(),
            'status' => 'running',
        ], JSON_PRETTY_PRINT));

        // Run in background with nohup
        $envPrefix = 'ANTHROPIC_API_KEY=' . escapeshellarg($request->api_key);
        $bgCmd = "nohup env {$envPrefix} {$shellCmd} > " . escapeshellarg($logFile) . " 2>&1 & echo $!";

        $pid = trim(shell_exec($bgCmd));
        file_put_contents($pidFile, $pid);

        return response()->json([
            'task_id' => $taskId,
            'pid' => $pid,
            'status' => 'launched',
        ]);
    }

    // ── List web-launched tasks ─────────────────────────────────────────────

    public function tasks()
    {
        $logDir = $this->outputDir() . '/web-tasks';
        $tasks = [];

        if (is_dir($logDir)) {
            foreach (glob($logDir . '/*.json') as $metaFile) {
                $meta = json_decode(file_get_contents($metaFile), true);
                if (!$meta) continue;

                $taskId = $meta['task_id'];
                $pidFile = $logDir . '/' . $taskId . '.pid';
                $pid = file_exists($pidFile) ? trim(file_get_contents($pidFile)) : null;

                // Check if process is still running
                $isRunning = false;
                if ($pid && is_numeric($pid)) {
                    $isRunning = file_exists("/proc/{$pid}");
                }

                $meta['pid'] = $pid;
                $meta['is_running'] = $isRunning;
                if (!$isRunning && ($meta['status'] ?? '') === 'running') {
                    $meta['status'] = 'finished';
                }

                $tasks[] = $meta;
            }

            // Sort newest first
            usort($tasks, fn($a, $b) => ($b['started_at'] ?? '') <=> ($a['started_at'] ?? ''));
        }

        return response()->json(['tasks' => $tasks]);
    }

    // ── Task logs ───────────────────────────────────────────────────────────

    public function taskLogs(string $taskId)
    {
        $logFile = $this->outputDir() . '/web-tasks/' . basename($taskId) . '.log';
        $pidFile = $this->outputDir() . '/web-tasks/' . basename($taskId) . '.pid';

        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log not found'], 404);
        }

        $pid = file_exists($pidFile) ? trim(file_get_contents($pidFile)) : null;
        $isRunning = $pid && is_numeric($pid) && file_exists("/proc/{$pid}");

        // Read last portion of log (limit to 500KB)
        $size = filesize($logFile);
        $maxBytes = 512 * 1024;
        $content = '';
        if ($size > $maxBytes) {
            $fh = fopen($logFile, 'r');
            fseek($fh, $size - $maxBytes);
            fgets($fh); // skip partial line
            $content = "... (truncated) ...\n" . fread($fh, $maxBytes);
            fclose($fh);
        } else {
            $content = file_get_contents($logFile);
        }

        return response()->json([
            'logs' => $content,
            'is_running' => $isRunning,
        ]);
    }

    // ── Stop a task ─────────────────────────────────────────────────────────

    public function stopTask(string $taskId)
    {
        $pidFile = $this->outputDir() . '/web-tasks/' . basename($taskId) . '.pid';
        $metaFile = $this->outputDir() . '/web-tasks/' . basename($taskId) . '.json';

        if (!file_exists($pidFile)) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $pid = trim(file_get_contents($pidFile));
        if (is_numeric($pid) && file_exists("/proc/{$pid}")) {
            // Kill the process group to stop the worker + docker
            posix_kill((int)$pid, SIGTERM);
            // Also try to stop any docker container it spawned
            shell_exec("docker ps --filter 'name=claude-worker-' --format '{{.Names}}' | xargs -r docker stop 2>/dev/null &");
        }

        // Update metadata
        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?: [];
            $meta['status'] = 'stopped';
            $meta['stopped_at'] = now()->toIso8601String();
            file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
        }

        return response()->json(['status' => 'stopped']);
    }

    // ── List completed runs ─────────────────────────────────────────────────

    public function runs()
    {
        $outputDir = $this->outputDir();
        $runs = [];

        if (is_dir($outputDir)) {
            $dirs = array_filter(glob($outputDir . '/*'), 'is_dir');
            // Sort newest first
            usort($dirs, fn($a, $b) => strcmp(basename($b), basename($a)));

            foreach ($dirs as $dir) {
                $name = basename($dir);
                // Skip the web-tasks metadata directory
                if ($name === 'web-tasks') continue;

                $info = [];
                $result = [];
                $infoFile = $dir . '/run-info.json';
                $resultFile = $dir . '/run-result.json';

                if (file_exists($infoFile)) {
                    $info = json_decode(file_get_contents($infoFile), true) ?: [];
                }
                if (file_exists($resultFile)) {
                    $result = json_decode(file_get_contents($resultFile), true) ?: [];
                }

                $patchFile = $dir . '/diffs/full.patch';
                $hasDiff = file_exists($patchFile) && filesize($patchFile) > 0;

                $runs[] = [
                    'run_id' => $name,
                    'info' => $info,
                    'result' => $result,
                    'has_diff' => $hasDiff,
                ];
            }
        }

        return response()->json(['runs' => $runs]);
    }

    // ── Run diff ────────────────────────────────────────────────────────────

    public function runDiff(string $runId)
    {
        $dir = $this->findRunDir($runId);
        if (!$dir) {
            return response()->json(['error' => 'Run not found'], 404);
        }

        $patchFile = $dir . '/diffs/full.patch';
        if (!file_exists($patchFile) || filesize($patchFile) === 0) {
            return response()->json(['error' => 'No diff available'], 404);
        }

        return response()->json(['diff' => file_get_contents($patchFile)]);
    }

    // ── Run summary ─────────────────────────────────────────────────────────

    public function runSummary(string $runId)
    {
        $dir = $this->findRunDir($runId);
        if (!$dir) {
            return response()->json(['error' => 'Run not found'], 404);
        }

        $summaryFile = $dir . '/diffs/summary.txt';
        $content = file_exists($summaryFile) ? file_get_contents($summaryFile) : '(no summary)';

        return response()->json(['summary' => $content]);
    }

    // ── Run logs ────────────────────────────────────────────────────────────

    public function runLogs(string $runId)
    {
        $dir = $this->findRunDir($runId);
        if (!$dir) {
            return response()->json(['error' => 'Run not found'], 404);
        }

        $logFile = $dir . '/logs/worker.log';
        $content = file_exists($logFile) ? file_get_contents($logFile) : '(no logs)';

        return response()->json(['logs' => $content]);
    }

    // ── Delete a run ────────────────────────────────────────────────────────

    public function deleteRun(string $runId)
    {
        $dir = $this->findRunDir($runId);
        if (!$dir) {
            return response()->json(['error' => 'Run not found'], 404);
        }

        // Recursively delete
        $this->deleteDirectory($dir);

        return response()->json(['status' => 'deleted']);
    }

    // ── Docker: list workers ────────────────────────────────────────────────

    public function dockerWorkers()
    {
        $result = Process::timeout(5)->run(
            'docker ps --filter "name=claude-worker-" --format "{{.ID}}\t{{.Names}}\t{{.Status}}\t{{.CreatedAt}}"'
        );

        $workers = [];
        if ($result->successful() && trim($result->output())) {
            foreach (explode("\n", trim($result->output())) as $line) {
                $parts = explode("\t", $line);
                if (count($parts) >= 4) {
                    $workers[] = [
                        'id' => substr($parts[0], 0, 12),
                        'name' => $parts[1],
                        'status' => $parts[2],
                        'created' => $parts[3],
                    ];
                }
            }
        }

        return response()->json(['workers' => $workers]);
    }

    // ── Docker: get logs ────────────────────────────────────────────────────

    public function dockerLogs(string $name)
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        $result = Process::timeout(10)->run(
            "docker logs --tail 300 " . escapeshellarg($safeName)
        );

        return response()->json([
            'logs' => $result->output() . $result->errorOutput(),
        ]);
    }

    // ── Docker: stop container ──────────────────────────────────────────────

    public function dockerStop(string $name)
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
        Process::timeout(15)->run("docker stop " . escapeshellarg($safeName));

        return response()->json(['status' => 'stopped']);
    }

    // ── Docker: stop all ────────────────────────────────────────────────────

    public function dockerStopAll()
    {
        $result = Process::timeout(5)->run(
            'docker ps --filter "name=claude-worker-" --format "{{.Names}}"'
        );

        $count = 0;
        if ($result->successful() && trim($result->output())) {
            foreach (explode("\n", trim($result->output())) as $name) {
                $name = trim($name);
                if ($name) {
                    Process::timeout(15)->run("docker stop " . escapeshellarg($name));
                    $count++;
                }
            }
        }

        return response()->json(['status' => 'all stopped', 'count' => $count]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    protected function findRunDir(string $runId): ?string
    {
        $runId = basename($runId); // prevent directory traversal
        $outputDir = $this->outputDir();

        if (!is_dir($outputDir)) {
            return null;
        }

        // Exact match
        $exact = $outputDir . '/' . $runId;
        if (is_dir($exact)) {
            return $exact;
        }

        // Prefix match
        foreach (glob($outputDir . '/' . $runId . '*') as $dir) {
            if (is_dir($dir)) {
                return $dir;
            }
        }

        return null;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
