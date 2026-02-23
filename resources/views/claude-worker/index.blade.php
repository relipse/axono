@extends('layouts.claude-worker')
@section('title', 'Admin')

@section('content')
<div class="pf-mb-6">
    <h1 class="pf-text-2xl pf-font-bold">Claude Worker Admin</h1>
    <p class="pf-text-sm pf-text-muted pf-mt-1">
        Launch, monitor, and review Claude Code worker tasks running in Docker containers.
    </p>
</div>

{{-- Tab Navigation --}}
<div class="cw-tabs pf-mb-6">
    <button class="cw-tab active" data-tab="new-task">New Task</button>
    <button class="cw-tab" data-tab="live-tasks">Live Tasks <span id="liveCount" class="cw-tab-badge" style="display:none"></span></button>
    <button class="cw-tab" data-tab="runs">Completed Runs</button>
    <button class="cw-tab" data-tab="docker">Docker</button>
</div>

{{-- ═══ New Task ═══ --}}
<div class="cw-panel active" id="panel-new-task">
    <div class="pf-card">
        <div class="pf-card-header">
            <h3>Launch a New Worker Task</h3>
        </div>
        <div class="pf-card-body">
            <div class="pf-form-group">
                <label class="pf-label">API Key</label>
                <div class="pf-flex pf-gap-2 pf-items-center">
                    <input type="password" class="pf-input" id="apiKey" placeholder="sk-ant-..." style="flex:1">
                    <label class="pf-text-sm pf-text-muted" style="white-space:nowrap">
                        <input type="checkbox" id="showKey" class="pf-checkbox" onchange="document.getElementById('apiKey').type = this.checked ? 'text' : 'password'"> Show
                    </label>
                </div>
                <p class="pf-hint">Saved in your browser only, sent per-request.</p>
            </div>

            <div class="pf-form-group">
                <label class="pf-label">Repository Source</label>
                <div class="pf-flex pf-gap-4">
                    <label class="pf-text-sm"><input type="radio" name="repoSource" value="url" checked onchange="toggleRepoSource()"> URL</label>
                    <label class="pf-text-sm"><input type="radio" name="repoSource" value="local" onchange="toggleRepoSource()"> Local Directory</label>
                </div>
            </div>

            <div class="pf-form-group" id="repoUrlGroup">
                <label class="pf-label">Repo URL</label>
                <input type="text" class="pf-input" id="repoUrl" placeholder="https://github.com/user/repo.git">
            </div>
            <div class="pf-form-group" id="localRepoGroup" style="display:none">
                <label class="pf-label">Local Repo Path</label>
                <input type="text" class="pf-input" id="localRepo" placeholder="/path/to/local/repo">
            </div>

            <div class="pf-form-group">
                <label class="pf-label">Task / Prompt</label>
                <div style="position:relative">
                    <textarea class="pf-textarea" id="taskPrompt" rows="3" placeholder="Describe what Claude should do..." style="padding-right:3rem"></textarea>
                    <button type="button" class="cw-mic-btn" id="micBtn" onclick="toggleVoice()" title="Voice dictation">
                        <svg id="micIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                            <line x1="8" y1="23" x2="16" y2="23"/>
                        </svg>
                        <span class="cw-mic-pulse" id="micPulse"></span>
                    </button>
                </div>
                <p class="pf-hint" id="voiceHint">Tap the mic to dictate your task.</p>
            </div>

            <div class="pf-grid pf-grid-2">
                <div class="pf-form-group">
                    <label class="pf-label">Branch Name <span class="pf-text-muted">(optional)</span></label>
                    <input type="text" class="pf-input" id="branchName" placeholder="claude/my-feature">
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Repo Branch <span class="pf-text-muted">(optional)</span></label>
                    <input type="text" class="pf-input" id="repoBranch" placeholder="main">
                </div>
            </div>

            <div class="pf-grid pf-grid-2">
                <div class="pf-form-group">
                    <label class="pf-label">Model</label>
                    <select class="pf-select" id="modelSelect">
                        <option value="">Default</option>
                        <option value="claude-opus-4-20250514">Claude Opus 4</option>
                        <option value="claude-sonnet-4-20250514">Claude Sonnet 4</option>
                        <option value="claude-haiku-4-5-20251001">Claude Haiku 4.5</option>
                    </select>
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Max Turns</label>
                    <input type="number" class="pf-input" id="maxTurns" placeholder="unlimited" min="1">
                </div>
            </div>

            <div class="pf-form-group">
                <div class="pf-flex pf-gap-4" style="flex-wrap:wrap">
                    <label class="pf-checkbox-group"><input type="checkbox" class="pf-checkbox" id="chkPush"> Push to remote</label>
                    <label class="pf-checkbox-group"><input type="checkbox" class="pf-checkbox" id="chkRebuild"> Rebuild image</label>
                    <label class="pf-checkbox-group"><input type="checkbox" class="pf-checkbox" id="chkVerbose"> Verbose</label>
                </div>
            </div>

            <div class="pf-actions-bar">
                <button class="pf-btn pf-btn-primary" onclick="launchTask()">Run Worker</button>
                <button class="pf-btn pf-btn-secondary" onclick="launchTask(true)">Dry Run</button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Live Tasks ═══ --}}
<div class="cw-panel" id="panel-live-tasks">
    <div class="pf-card">
        <div class="pf-card-header">
            <h3>Running Tasks</h3>
            <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="refreshLiveTasks()">Refresh</button>
        </div>
        <div class="pf-card-body" id="liveTasksContainer">
            <p class="pf-text-center pf-text-muted">No running tasks</p>
        </div>
    </div>
</div>

{{-- ═══ Completed Runs ═══ --}}
<div class="cw-panel" id="panel-runs">
    <div class="pf-card">
        <div class="pf-card-header">
            <h3>Completed Runs</h3>
            <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="refreshRuns()">Refresh</button>
        </div>
        <div id="runsTableContainer">
            <p class="pf-card-body pf-text-center pf-text-muted">Loading...</p>
        </div>
    </div>
</div>

{{-- ═══ Docker Workers ═══ --}}
<div class="cw-panel" id="panel-docker">
    <div class="pf-card">
        <div class="pf-card-header">
            <h3>Docker Containers</h3>
            <div class="pf-flex pf-gap-2">
                <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="refreshDocker()">Refresh</button>
                <button class="pf-btn pf-btn-danger pf-btn-sm" onclick="stopAllDocker()">Stop All</button>
            </div>
        </div>
        <div id="dockerTableContainer">
            <p class="pf-card-body pf-text-center pf-text-muted">Loading...</p>
        </div>
    </div>
</div>

{{-- ═══ Modal ═══ --}}
<div class="cw-modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="cw-modal" onclick="event.stopPropagation()">
        <div class="cw-modal-header">
            <h3 id="modalTitle">Details</h3>
            <button class="pf-btn pf-btn-ghost pf-btn-sm" onclick="hideModal()">&times;</button>
        </div>
        <div class="cw-modal-body" id="modalBody"></div>
    </div>
</div>
@endsection

@section('scripts')
<style>
/* ── Tab styles ─────────────────────────────────────────────────── */
.cw-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--pf-gray-200);
    padding-bottom: 0;
}
.cw-tab {
    padding: 0.625rem 1.125rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--pf-gray-500);
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    font-family: var(--pf-font);
}
.cw-tab:hover { color: var(--pf-gray-800); }
.cw-tab.active {
    color: var(--pf-primary-600);
    border-bottom-color: var(--pf-primary-600);
}
.cw-tab-badge {
    background: var(--pf-primary-100);
    color: var(--pf-primary-700);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 9999px;
    margin-left: 4px;
}

/* ── Panel toggle ───────────────────────────────────────────────── */
.cw-panel { display: none; }
.cw-panel.active { display: block; }

/* ── Task card (live tasks) ─────────────────────────────────────── */
.cw-task-card {
    background: var(--pf-gray-50);
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-md);
    padding: 1rem;
    margin-bottom: 0.75rem;
}
.cw-task-card:last-child { margin-bottom: 0; }

/* ── Log viewer ─────────────────────────────────────────────────── */
.cw-log-viewer {
    background: var(--pf-gray-900);
    color: #d4d4d4;
    border-radius: var(--pf-radius-md);
    padding: 1rem;
    font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
    font-size: 0.75rem;
    line-height: 1.6;
    max-height: 500px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

/* ── Diff viewer ────────────────────────────────────────────────── */
.cw-diff-viewer {
    background: var(--pf-gray-900);
    color: #d4d4d4;
    border-radius: var(--pf-radius-md);
    padding: 1rem;
    font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
    font-size: 0.75rem;
    line-height: 1.5;
    max-height: 600px;
    overflow: auto;
    white-space: pre;
}
.cw-diff-add { color: #22c55e; background: rgba(34,197,94,0.08); }
.cw-diff-del { color: #ef4444; background: rgba(239,68,68,0.08); }
.cw-diff-hunk { color: #818cf8; }
.cw-diff-header { color: #58a6ff; font-weight: bold; }

/* ── Modal ──────────────────────────────────────────────────────── */
.cw-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 100;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(2px);
}
.cw-modal-overlay.active { display: flex; }
.cw-modal {
    background: #fff;
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-lg);
    width: 90%;
    max-width: 960px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: var(--pf-shadow-xl);
}
.cw-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--pf-gray-200);
}
.cw-modal-header h3 { font-size: 1rem; font-weight: 600; }
.cw-modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

/* ── Spinner ────────────────────────────────────────────────────── */
@keyframes cw-spin { to { transform: rotate(360deg); } }
.cw-spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid var(--pf-gray-300);
    border-top-color: var(--pf-primary-600);
    border-radius: 50%;
    animation: cw-spin 0.6s linear infinite;
    margin-right: 6px;
    vertical-align: middle;
}

/* ── Mic button ────────────────────────────────────────────────── */
.cw-mic-btn {
    position: absolute;
    right: 8px;
    top: 8px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--pf-gray-300);
    background: #fff;
    color: var(--pf-gray-500);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}
.cw-mic-btn:hover { border-color: var(--pf-primary-600); color: var(--pf-primary-600); }
.cw-mic-btn.recording {
    border-color: #ef4444;
    color: #ef4444;
    background: #fef2f2;
}
.cw-mic-pulse {
    display: none;
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    border: 2px solid #ef4444;
    animation: cw-mic-pulse 1.2s ease-in-out infinite;
}
.cw-mic-btn.recording .cw-mic-pulse { display: block; }
@keyframes cw-mic-pulse {
    0%, 100% { opacity: 0; transform: scale(0.9); }
    50% { opacity: 1; transform: scale(1.1); }
}
</style>

<script>
const CSRF = '{{ csrf_token() }}';

// ── Tab navigation ──────────────────────────────────────────────────────────
document.querySelectorAll('.cw-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.cw-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.cw-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('panel-' + tab.dataset.tab).classList.add('active');

        const t = tab.dataset.tab;
        if (t === 'runs') refreshRuns();
        else if (t === 'docker') refreshDocker();
        else if (t === 'live-tasks') refreshLiveTasks();
    });
});

// ── Settings (localStorage) ─────────────────────────────────────────────────
(function loadSettings() {
    const key = localStorage.getItem('cw_api_key') || '';
    document.getElementById('apiKey').value = key;
})();

document.getElementById('apiKey').addEventListener('change', function() {
    localStorage.setItem('cw_api_key', this.value.trim());
});

// ── Repo source toggle ──────────────────────────────────────────────────────
function toggleRepoSource() {
    const val = document.querySelector('input[name="repoSource"]:checked').value;
    document.getElementById('repoUrlGroup').style.display = val === 'url' ? '' : 'none';
    document.getElementById('localRepoGroup').style.display = val === 'local' ? '' : 'none';
}

// ── API helper ──────────────────────────────────────────────────────────────
async function api(method, path, body) {
    const opts = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        }
    };
    if (body) opts.body = JSON.stringify(body);
    const resp = await fetch(path, opts);
    return resp.json();
}

// ── Launch Task ─────────────────────────────────────────────────────────────
async function launchTask(dryRun) {
    const repoSource = document.querySelector('input[name="repoSource"]:checked').value;
    const params = {
        api_key: document.getElementById('apiKey').value.trim(),
        repo_source: repoSource,
        repo_url: document.getElementById('repoUrl').value.trim(),
        local_repo: document.getElementById('localRepo').value.trim(),
        task: document.getElementById('taskPrompt').value.trim(),
        branch: document.getElementById('branchName').value.trim(),
        repo_branch: document.getElementById('repoBranch').value.trim(),
        model: document.getElementById('modelSelect').value,
        max_turns: document.getElementById('maxTurns').value,
        push: document.getElementById('chkPush').checked,
        rebuild: document.getElementById('chkRebuild').checked,
        verbose: document.getElementById('chkVerbose').checked,
    };

    if (!params.task) return alert('Task is required.');
    if (repoSource === 'url' && !params.repo_url) return alert('Repo URL is required.');
    if (repoSource === 'local' && !params.local_repo) return alert('Local repo path is required.');
    if (!params.api_key) return alert('API key is required.');

    // Save the key
    localStorage.setItem('cw_api_key', params.api_key);

    const data = await api('POST', '{{ route("claude-worker.launch") }}', params);
    if (data.error || data.errors) {
        const msg = data.error || Object.values(data.errors).flat().join('\n');
        return alert('Error: ' + msg);
    }

    // Switch to live tasks tab
    document.querySelector('.cw-tab[data-tab="live-tasks"]').click();
    refreshLiveTasks();
}

// ── Live Tasks ──────────────────────────────────────────────────────────────
let liveRefreshTimer = null;

async function refreshLiveTasks() {
    const data = await api('GET', '{{ route("claude-worker.tasks") }}');
    const container = document.getElementById('liveTasksContainer');
    const tasks = data.tasks || [];

    // Update badge
    const running = tasks.filter(t => t.is_running);
    const badge = document.getElementById('liveCount');
    if (running.length > 0) {
        badge.textContent = running.length;
        badge.style.display = '';
    } else {
        badge.style.display = 'none';
    }

    if (tasks.length === 0) {
        container.innerHTML = '<p class="pf-text-center pf-text-muted">No tasks launched from this admin session</p>';
        return;
    }

    container.innerHTML = tasks.map(t => {
        const badgeCls = t.is_running ? 'pf-badge-blue' :
                         t.status === 'stopped' ? 'pf-badge-red' : 'pf-badge-green';
        const statusText = t.is_running ? 'running' : (t.status || 'finished');
        return `
            <div class="cw-task-card">
                <div class="pf-flex pf-items-center pf-justify-between pf-mb-2">
                    <span class="pf-font-semibold pf-text-sm">${esc(t.task)}</span>
                    <span class="pf-badge ${badgeCls}">
                        ${t.is_running ? '<span class="cw-spinner"></span>' : ''}${esc(statusText)}
                    </span>
                </div>
                <p class="pf-text-xs pf-text-muted pf-mb-2">
                    ID: ${esc(t.task_id)} &middot; Repo: ${esc(t.repo || '?')} &middot; Started: ${esc(t.started_at || '?')}
                </p>
                <div class="pf-flex pf-gap-2">
                    <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="viewTaskLogs('${esc(t.task_id)}')">View Logs</button>
                    ${t.is_running ? `<button class="pf-btn pf-btn-danger pf-btn-sm" onclick="stopTask('${esc(t.task_id)}')">Stop</button>` : ''}
                </div>
            </div>`;
    }).join('');

    // Auto-refresh if any are running
    clearTimeout(liveRefreshTimer);
    if (running.length > 0) {
        liveRefreshTimer = setTimeout(refreshLiveTasks, 4000);
    }
}

async function viewTaskLogs(taskId) {
    const data = await api('GET', '/claude-worker/tasks/' + taskId + '/logs');
    showModal('Logs — ' + taskId,
        '<div class="cw-log-viewer" id="logViewer">' + esc(data.logs || '(no output yet)') + '</div>' +
        '<div class="pf-mt-4"><button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="refreshTaskLogs(\'' + taskId + '\')">Refresh Logs</button></div>');

    if (data.is_running) {
        window._logPollId = taskId;
        pollLogs(taskId);
    }
}

function pollLogs(taskId) {
    if (window._logPollId !== taskId) return;
    setTimeout(async () => {
        if (window._logPollId !== taskId) return;
        try {
            const data = await api('GET', '/claude-worker/tasks/' + taskId + '/logs');
            const el = document.getElementById('logViewer');
            if (el) {
                el.textContent = data.logs || '(no output yet)';
                el.scrollTop = el.scrollHeight;
            }
            if (data.is_running) pollLogs(taskId);
        } catch(e) {}
    }, 2500);
}

async function refreshTaskLogs(taskId) {
    const data = await api('GET', '/claude-worker/tasks/' + taskId + '/logs');
    const el = document.getElementById('logViewer');
    if (el) {
        el.textContent = data.logs || '(no output yet)';
        el.scrollTop = el.scrollHeight;
    }
}

async function stopTask(taskId) {
    if (!confirm('Stop this task?')) return;
    await api('POST', '/claude-worker/tasks/' + taskId + '/stop');
    refreshLiveTasks();
}

// ── Completed Runs ──────────────────────────────────────────────────────────
async function refreshRuns() {
    const data = await api('GET', '{{ route("claude-worker.runs") }}');
    const container = document.getElementById('runsTableContainer');
    const runs = data.runs || [];

    if (runs.length === 0) {
        container.innerHTML = '<p class="pf-card-body pf-text-center pf-text-muted">No completed runs found in output/</p>';
        return;
    }

    container.innerHTML = `<table class="pf-table">
        <thead><tr>
            <th>Run ID</th><th>Status</th><th>Files</th><th>Branch</th><th>Task</th><th>Diff</th><th></th>
        </tr></thead>
        <tbody>` +
        runs.map(r => {
            const status = (r.result && r.result.status) || '?';
            const files = (r.result && r.result.files_changed) || '?';
            const branch = (r.info && r.info.branch_name) || '?';
            const task = ((r.info && r.info.task) || '?').substring(0, 60);
            const badgeCls = status === 'success' ? 'pf-badge-green' :
                             status === 'error' ? 'pf-badge-red' : 'pf-badge-gray';
            return `<tr>
                <td><span class="pf-font-mono pf-text-xs">${esc(r.run_id)}</span></td>
                <td><span class="pf-badge ${badgeCls}">${esc(status)}</span></td>
                <td>${esc(String(files))}</td>
                <td><span class="pf-text-xs" style="color:var(--pf-primary-600)">${esc(branch)}</span></td>
                <td><span class="pf-text-xs pf-text-muted">${esc(task)}</span></td>
                <td>${r.has_diff ? '<span class="pf-badge pf-badge-green">yes</span>' : '<span class="pf-badge pf-badge-gray">no</span>'}</td>
                <td style="white-space:nowrap">
                    ${r.has_diff ? `<button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="viewRunDiff('${esc(r.run_id)}')">Diff</button> ` : ''}
                    <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="viewRunSummary('${esc(r.run_id)}')">Summary</button>
                    <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="viewRunLogs('${esc(r.run_id)}')">Log</button>
                    <button class="pf-btn pf-btn-danger pf-btn-sm" onclick="deleteRun('${esc(r.run_id)}')">Del</button>
                </td>
            </tr>`;
        }).join('') +
        '</tbody></table>';
}

async function viewRunDiff(runId) {
    const data = await api('GET', '/claude-worker/runs/' + runId + '/diff');
    if (data.error) return alert(data.error);
    showModal('Diff — ' + runId,
        '<div class="cw-diff-viewer">' + highlightDiff(data.diff || '') + '</div>');
}

async function viewRunSummary(runId) {
    const data = await api('GET', '/claude-worker/runs/' + runId + '/summary');
    showModal('Summary — ' + runId,
        '<div class="cw-log-viewer">' + esc(data.summary || '(no summary)') + '</div>');
}

async function viewRunLogs(runId) {
    const data = await api('GET', '/claude-worker/runs/' + runId + '/logs');
    showModal('Logs — ' + runId,
        '<div class="cw-log-viewer">' + esc(data.logs || '(no logs)') + '</div>');
}

async function deleteRun(runId) {
    if (!confirm('Delete run ' + runId + ' and all its files?')) return;
    await api('DELETE', '/claude-worker/runs/' + runId);
    refreshRuns();
}

// ── Docker Workers ──────────────────────────────────────────────────────────
async function refreshDocker() {
    const data = await api('GET', '{{ route("claude-worker.docker.workers") }}');
    const container = document.getElementById('dockerTableContainer');
    const workers = data.workers || [];

    if (workers.length === 0) {
        container.innerHTML = '<p class="pf-card-body pf-text-center pf-text-muted">No running Docker containers matching claude-worker-*</p>';
        return;
    }

    container.innerHTML = `<table class="pf-table">
        <thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>` +
        workers.map(w => `<tr>
            <td><span class="pf-font-mono pf-text-xs">${esc(w.id)}</span></td>
            <td>${esc(w.name)}</td>
            <td><span class="pf-badge pf-badge-green">${esc(w.status)}</span></td>
            <td class="pf-text-xs pf-text-muted">${esc(w.created)}</td>
            <td style="white-space:nowrap">
                <button class="pf-btn pf-btn-secondary pf-btn-sm" onclick="viewDockerLogs('${esc(w.name)}')">Logs</button>
                <button class="pf-btn pf-btn-danger pf-btn-sm" onclick="stopDocker('${esc(w.name)}')">Stop</button>
            </td>
        </tr>`).join('') +
        '</tbody></table>';
}

async function viewDockerLogs(name) {
    const data = await api('GET', '/claude-worker/docker/logs/' + encodeURIComponent(name));
    showModal('Docker Logs — ' + name,
        '<div class="cw-log-viewer">' + esc(data.logs || '(empty)') + '</div>');
}

async function stopDocker(name) {
    if (!confirm('Stop container ' + name + '?')) return;
    await api('POST', '/claude-worker/docker/stop/' + encodeURIComponent(name));
    refreshDocker();
}

async function stopAllDocker() {
    if (!confirm('Stop ALL running claude-worker containers?')) return;
    await api('POST', '{{ route("claude-worker.docker.stop-all") }}');
    refreshDocker();
}

// ── Modal ───────────────────────────────────────────────────────────────────
function showModal(title, bodyHtml) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = bodyHtml;
    document.getElementById('modalOverlay').classList.add('active');
}
function hideModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    window._logPollId = null;
}
function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) hideModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideModal(); });

// ── Helpers ─────────────────────────────────────────────────────────────────
function esc(s) {
    if (s == null) return '';
    const d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
}

function highlightDiff(text) {
    return text.split('\n').map(line => {
        const e = esc(line);
        if (line.startsWith('+++') || line.startsWith('---')) return '<span class="cw-diff-header">' + e + '</span>';
        if (line.startsWith('+')) return '<span class="cw-diff-add">' + e + '</span>';
        if (line.startsWith('-')) return '<span class="cw-diff-del">' + e + '</span>';
        if (line.startsWith('@@')) return '<span class="cw-diff-hunk">' + e + '</span>';
        if (line.startsWith('diff ')) return '<span class="cw-diff-header">' + e + '</span>';
        return e;
    }).join('\n');
}

// ── Voice Dictation ─────────────────────────────────────────────────────────
let voiceRecognition = null;
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;

// Prefer Web Speech API (works in Safari iOS, Chrome), fall back to Whisper API
const hasSpeechAPI = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;

function toggleVoice() {
    if (isRecording) {
        stopVoice();
    } else {
        startVoice();
    }
}

function startVoice() {
    const btn = document.getElementById('micBtn');
    const hint = document.getElementById('voiceHint');

    if (hasSpeechAPI) {
        startWebSpeech(btn, hint);
    } else {
        startWhisperRecording(btn, hint);
    }
}

function stopVoice() {
    const btn = document.getElementById('micBtn');
    const hint = document.getElementById('voiceHint');

    if (voiceRecognition) {
        voiceRecognition.stop();
        voiceRecognition = null;
    }
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    btn.classList.remove('recording');
    hint.textContent = 'Tap the mic to dictate your task.';
    isRecording = false;
}

// ── Web Speech API (built-in, works on iOS Safari & Chrome) ─────────────────
function startWebSpeech(btn, hint) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    voiceRecognition = new SpeechRecognition();
    voiceRecognition.continuous = true;
    voiceRecognition.interimResults = true;
    voiceRecognition.lang = 'en-US';

    let finalTranscript = '';

    voiceRecognition.onstart = () => {
        isRecording = true;
        btn.classList.add('recording');
        hint.textContent = 'Listening... tap mic to stop.';
        hint.style.color = '#ef4444';
    };

    voiceRecognition.onresult = (event) => {
        let interim = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript + ' ';
            } else {
                interim += transcript;
            }
        }
        const ta = document.getElementById('taskPrompt');
        const existing = ta.value.replace(/\n\[listening\.\.\.\].*$/, '');
        ta.value = (existing ? existing + '\n' : '') + finalTranscript + (interim ? '[listening...] ' + interim : '');
    };

    voiceRecognition.onerror = (event) => {
        if (event.error === 'not-allowed') {
            hint.textContent = 'Microphone access denied. Check browser permissions.';
        } else {
            hint.textContent = 'Speech error: ' + event.error + '. Trying Whisper fallback...';
            // Fall back to Whisper recording
            startWhisperRecording(btn, hint);
            return;
        }
        hint.style.color = '#ef4444';
        btn.classList.remove('recording');
        isRecording = false;
    };

    voiceRecognition.onend = () => {
        if (isRecording) {
            // Clean up the "[listening...]" marker
            const ta = document.getElementById('taskPrompt');
            ta.value = ta.value.replace(/\[listening\.\.\.\]\s*/g, '').trim();
        }
        btn.classList.remove('recording');
        hint.textContent = 'Tap the mic to dictate your task.';
        hint.style.color = '';
        isRecording = false;
        voiceRecognition = null;
    };

    voiceRecognition.start();
}

// ── Whisper API fallback (record audio, send to server for transcription) ────
async function startWhisperRecording(btn, hint) {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream, { mimeType: getSupportedMimeType() });

        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = async () => {
            stream.getTracks().forEach(t => t.stop());
            btn.classList.remove('recording');
            hint.textContent = 'Transcribing with Whisper...';
            hint.style.color = 'var(--pf-primary-600)';

            const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType });
            await transcribeWithWhisper(blob, hint);
        };

        mediaRecorder.start(1000); // collect chunks every second
        isRecording = true;
        btn.classList.add('recording');
        hint.textContent = 'Recording... tap mic to stop and transcribe.';
        hint.style.color = '#ef4444';
    } catch (err) {
        hint.textContent = 'Microphone access denied. Check browser permissions.';
        hint.style.color = '#ef4444';
    }
}

async function transcribeWithWhisper(audioBlob, hint) {
    const openaiKey = localStorage.getItem('cw_openai_key') || '';

    // Try server-side transcription endpoint first
    const formData = new FormData();
    formData.append('audio', audioBlob, 'recording.webm');
    if (openaiKey) formData.append('openai_key', openaiKey);

    try {
        const resp = await fetch('/claude-worker/transcribe', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: formData,
        });
        const data = await resp.json();
        if (data.text) {
            const ta = document.getElementById('taskPrompt');
            ta.value = (ta.value ? ta.value + '\n' : '') + data.text;
            hint.textContent = 'Transcription complete. Tap mic to record more.';
            hint.style.color = 'var(--pf-green-600, #16a34a)';
        } else {
            hint.textContent = 'Transcription failed: ' + (data.error || 'Unknown error');
            hint.style.color = '#ef4444';
        }
    } catch (err) {
        hint.textContent = 'Transcription request failed: ' + err.message;
        hint.style.color = '#ef4444';
    }
}

function getSupportedMimeType() {
    const types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus'];
    for (const type of types) {
        if (MediaRecorder.isTypeSupported(type)) return type;
    }
    return 'audio/webm';
}

// ── Init ────────────────────────────────────────────────────────────────────
refreshRuns();
refreshLiveTasks();
</script>
@endsection
