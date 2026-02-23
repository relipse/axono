@extends('layouts.claude-worker')
@section('title', 'Voice Task')

@section('content')
<div class="vp-container">
    <h1 class="vp-title">Voice Task</h1>
    <p class="vp-subtitle">Speak your task, then launch it.</p>

    {{-- Big mic button --}}
    <div class="vp-mic-area">
        <button type="button" class="vp-mic-btn" id="vpMicBtn" onclick="vpToggle()">
            <svg id="vpMicIcon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                <line x1="12" y1="19" x2="12" y2="23"/>
                <line x1="8" y1="23" x2="16" y2="23"/>
            </svg>
            <span class="vp-mic-ring" id="vpMicRing"></span>
        </button>
        <p class="vp-status" id="vpStatus">Tap to start speaking</p>
    </div>

    {{-- Transcribed text --}}
    <div class="pf-form-group">
        <label class="pf-label">Task</label>
        <textarea class="pf-textarea" id="vpTask" rows="4" placeholder="Your spoken task will appear here..."></textarea>
    </div>

    {{-- Quick settings --}}
    <details class="vp-settings">
        <summary class="pf-label" style="cursor:pointer; user-select:none;">Settings</summary>
        <div class="pf-mt-2">
            <div class="pf-form-group">
                <label class="pf-label pf-text-sm">API Key</label>
                <input type="password" class="pf-input" id="vpApiKey" placeholder="sk-ant-...">
            </div>
            <div class="pf-form-group">
                <label class="pf-label pf-text-sm">OpenAI Key <span class="pf-text-muted">(for Whisper fallback)</span></label>
                <input type="password" class="pf-input" id="vpOpenAIKey" placeholder="sk-...">
            </div>
            <div class="pf-form-group">
                <label class="pf-label pf-text-sm">Repository Source</label>
                <div class="pf-flex pf-gap-4">
                    <label class="pf-text-sm"><input type="radio" name="vpRepoSource" value="url" checked onchange="vpToggleRepo()"> URL</label>
                    <label class="pf-text-sm"><input type="radio" name="vpRepoSource" value="local" onchange="vpToggleRepo()"> Local</label>
                </div>
            </div>
            <div id="vpRepoUrlGroup">
                <div class="pf-form-group">
                    <label class="pf-label pf-text-sm">Repo URL</label>
                    <input type="text" class="pf-input" id="vpRepoUrl" placeholder="https://github.com/user/repo.git">
                </div>
            </div>
            <div id="vpLocalRepoGroup" style="display:none">
                <div class="pf-form-group">
                    <label class="pf-label pf-text-sm">Local Repo Path</label>
                    <input type="text" class="pf-input" id="vpLocalRepo" placeholder="/path/to/repo">
                </div>
            </div>
            <div class="pf-form-group">
                <label class="pf-label pf-text-sm">Model</label>
                <select class="pf-select" id="vpModel">
                    <option value="">Default</option>
                    <option value="claude-opus-4-20250514">Claude Opus 4</option>
                    <option value="claude-sonnet-4-20250514">Claude Sonnet 4</option>
                    <option value="claude-haiku-4-5-20251001">Claude Haiku 4.5</option>
                </select>
            </div>
            <div class="pf-form-group">
                <label class="pf-checkbox-group pf-text-sm"><input type="checkbox" class="pf-checkbox" id="vpPush"> Push to remote</label>
            </div>
        </div>
    </details>

    {{-- Actions --}}
    <div class="vp-actions">
        <button class="pf-btn pf-btn-primary pf-btn-lg" onclick="vpLaunch()" style="flex:1">Launch Task</button>
        <button class="pf-btn pf-btn-secondary pf-btn-lg" onclick="document.getElementById('vpTask').value=''" style="flex:0">Clear</button>
    </div>

    {{-- Result --}}
    <div class="vp-result" id="vpResult" style="display:none">
        <div class="pf-alert pf-alert-success" id="vpResultMsg"></div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.vp-container { max-width: 480px; margin: 0 auto; }
.vp-title { font-size: 1.5rem; font-weight: 800; text-align: center; margin-bottom: 0.25rem; }
.vp-subtitle { text-align: center; color: var(--pf-gray-500); font-size: 0.875rem; margin-bottom: 1.5rem; }

.vp-mic-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.vp-mic-btn {
    position: relative;
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 3px solid var(--pf-gray-300);
    background: #fff;
    color: var(--pf-gray-500);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    -webkit-tap-highlight-color: transparent;
}
.vp-mic-btn:active { transform: scale(0.95); }
.vp-mic-btn.recording {
    border-color: #ef4444;
    color: #ef4444;
    background: #fef2f2;
    box-shadow: 0 0 0 4px rgba(239,68,68,0.15);
}
.vp-mic-btn.processing {
    border-color: var(--pf-primary-600);
    color: var(--pf-primary-600);
    background: var(--pf-primary-50, #eff6ff);
}

.vp-mic-ring {
    display: none;
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    border: 3px solid #ef4444;
    animation: vp-ring-pulse 1.4s ease-in-out infinite;
}
.vp-mic-btn.recording .vp-mic-ring { display: block; }
@keyframes vp-ring-pulse {
    0%, 100% { opacity: 0.2; transform: scale(0.95); }
    50% { opacity: 0.8; transform: scale(1.08); }
}

.vp-status {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: var(--pf-gray-500);
    text-align: center;
    min-height: 1.5rem;
}
.vp-status.active { color: #ef4444; font-weight: 600; }
.vp-status.success { color: #16a34a; }
.vp-status.error { color: #ef4444; }

.vp-settings {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--pf-gray-50);
    border: 1px solid var(--pf-gray-200);
    border-radius: var(--pf-radius-md);
}

.vp-actions {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.pf-btn-lg {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
}
</style>

<script>
const VP_CSRF = '{{ csrf_token() }}';
let vpRecognition = null;
let vpMediaRecorder = null;
let vpAudioChunks = [];
let vpIsRecording = false;

const vpHasSpeech = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;

// Load saved settings
(function() {
    const fields = {vpApiKey:'cw_api_key', vpOpenAIKey:'cw_openai_key', vpRepoUrl:'cw_repo_url',
                     vpLocalRepo:'cw_local_repo', vpModel:'cw_model'};
    for (const [id, key] of Object.entries(fields)) {
        const v = localStorage.getItem(key);
        if (v) document.getElementById(id).value = v;
    }
    const src = localStorage.getItem('cw_repo_source');
    if (src === 'local') {
        document.querySelector('input[name="vpRepoSource"][value="local"]').checked = true;
        vpToggleRepo();
    }
    if (localStorage.getItem('cw_push') === '1') document.getElementById('vpPush').checked = true;
})();

// Save settings on change
document.querySelectorAll('#vpApiKey,#vpOpenAIKey,#vpRepoUrl,#vpLocalRepo,#vpModel,#vpPush').forEach(el => {
    el.addEventListener('change', saveVpSettings);
});
document.querySelectorAll('input[name="vpRepoSource"]').forEach(el => {
    el.addEventListener('change', () => { vpToggleRepo(); saveVpSettings(); });
});

function saveVpSettings() {
    localStorage.setItem('cw_api_key', document.getElementById('vpApiKey').value.trim());
    localStorage.setItem('cw_openai_key', document.getElementById('vpOpenAIKey').value.trim());
    localStorage.setItem('cw_repo_url', document.getElementById('vpRepoUrl').value.trim());
    localStorage.setItem('cw_local_repo', document.getElementById('vpLocalRepo').value.trim());
    localStorage.setItem('cw_model', document.getElementById('vpModel').value);
    localStorage.setItem('cw_repo_source', document.querySelector('input[name="vpRepoSource"]:checked').value);
    localStorage.setItem('cw_push', document.getElementById('vpPush').checked ? '1' : '0');
}

function vpToggleRepo() {
    const val = document.querySelector('input[name="vpRepoSource"]:checked').value;
    document.getElementById('vpRepoUrlGroup').style.display = val === 'url' ? '' : 'none';
    document.getElementById('vpLocalRepoGroup').style.display = val === 'local' ? '' : 'none';
}

// ── Voice toggle ────────────────────────────────────────────────────────────
function vpToggle() {
    if (vpIsRecording) {
        vpStop();
    } else {
        vpStart();
    }
}

function vpStart() {
    const btn = document.getElementById('vpMicBtn');
    const status = document.getElementById('vpStatus');

    if (vpHasSpeech) {
        vpStartWebSpeech(btn, status);
    } else {
        vpStartWhisper(btn, status);
    }
}

function vpStop() {
    const btn = document.getElementById('vpMicBtn');
    const status = document.getElementById('vpStatus');

    if (vpRecognition) { vpRecognition.stop(); vpRecognition = null; }
    if (vpMediaRecorder && vpMediaRecorder.state !== 'inactive') { vpMediaRecorder.stop(); return; }

    btn.classList.remove('recording');
    status.textContent = 'Tap to start speaking';
    status.className = 'vp-status';
    vpIsRecording = false;
}

function vpStartWebSpeech(btn, status) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    vpRecognition = new SR();
    vpRecognition.continuous = true;
    vpRecognition.interimResults = true;
    vpRecognition.lang = 'en-US';

    let finalText = '';

    vpRecognition.onstart = () => {
        vpIsRecording = true;
        btn.classList.add('recording');
        status.textContent = 'Listening...';
        status.className = 'vp-status active';
    };

    vpRecognition.onresult = (e) => {
        let interim = '';
        for (let i = e.resultIndex; i < e.results.length; i++) {
            if (e.results[i].isFinal) {
                finalText += e.results[i][0].transcript + ' ';
            } else {
                interim += e.results[i][0].transcript;
            }
        }
        const ta = document.getElementById('vpTask');
        ta.value = finalText + interim;
        ta.scrollTop = ta.scrollHeight;
    };

    vpRecognition.onerror = (e) => {
        if (e.error === 'not-allowed') {
            status.textContent = 'Mic access denied. Check permissions.';
            status.className = 'vp-status error';
        } else {
            status.textContent = 'Speech error. Trying Whisper...';
            vpStartWhisper(btn, status);
            return;
        }
        btn.classList.remove('recording');
        vpIsRecording = false;
    };

    vpRecognition.onend = () => {
        btn.classList.remove('recording');
        status.textContent = 'Done. Tap mic to add more.';
        status.className = 'vp-status success';
        vpIsRecording = false;
        vpRecognition = null;
    };

    vpRecognition.start();
}

async function vpStartWhisper(btn, status) {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        vpAudioChunks = [];
        const mimeType = vpGetMime();
        vpMediaRecorder = new MediaRecorder(stream, { mimeType });

        vpMediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) vpAudioChunks.push(e.data);
        };

        vpMediaRecorder.onstop = async () => {
            stream.getTracks().forEach(t => t.stop());
            btn.classList.remove('recording');
            btn.classList.add('processing');
            status.textContent = 'Transcribing...';
            status.className = 'vp-status';
            vpIsRecording = false;

            const blob = new Blob(vpAudioChunks, { type: mimeType });
            await vpTranscribe(blob, btn, status);
        };

        vpMediaRecorder.start(1000);
        vpIsRecording = true;
        btn.classList.add('recording');
        status.textContent = 'Recording... tap to stop.';
        status.className = 'vp-status active';
    } catch (err) {
        status.textContent = 'Mic access denied.';
        status.className = 'vp-status error';
    }
}

async function vpTranscribe(blob, btn, status) {
    const openaiKey = document.getElementById('vpOpenAIKey').value.trim();
    const fd = new FormData();
    fd.append('audio', blob, 'recording.webm');
    if (openaiKey) fd.append('openai_key', openaiKey);

    try {
        const resp = await fetch('/claude-worker/transcribe', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': VP_CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await resp.json();
        btn.classList.remove('processing');

        if (data.text) {
            const ta = document.getElementById('vpTask');
            ta.value = (ta.value ? ta.value + ' ' : '') + data.text;
            status.textContent = 'Transcribed! Tap mic for more.';
            status.className = 'vp-status success';
        } else {
            status.textContent = data.error || 'Transcription failed.';
            status.className = 'vp-status error';
        }
    } catch (err) {
        btn.classList.remove('processing');
        status.textContent = 'Network error: ' + err.message;
        status.className = 'vp-status error';
    }
}

function vpGetMime() {
    const types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus'];
    for (const t of types) { if (MediaRecorder.isTypeSupported(t)) return t; }
    return 'audio/webm';
}

// ── Launch task ─────────────────────────────────────────────────────────────
async function vpLaunch() {
    const repoSource = document.querySelector('input[name="vpRepoSource"]:checked').value;
    const params = {
        api_key: document.getElementById('vpApiKey').value.trim(),
        repo_source: repoSource,
        repo_url: document.getElementById('vpRepoUrl').value.trim(),
        local_repo: document.getElementById('vpLocalRepo').value.trim(),
        task: document.getElementById('vpTask').value.trim(),
        model: document.getElementById('vpModel').value,
        push: document.getElementById('vpPush').checked,
    };

    if (!params.task) return alert('Record or type a task first.');
    if (!params.api_key) return alert('API key is required. Open Settings.');
    if (repoSource === 'url' && !params.repo_url) return alert('Repo URL is required. Open Settings.');
    if (repoSource === 'local' && !params.local_repo) return alert('Local repo path is required. Open Settings.');

    saveVpSettings();

    const resultDiv = document.getElementById('vpResult');
    const resultMsg = document.getElementById('vpResultMsg');
    resultDiv.style.display = 'none';

    try {
        const resp = await fetch('{{ route("claude-worker.launch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': VP_CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify(params),
        });
        const data = await resp.json();

        if (data.error || data.errors) {
            const msg = data.error || Object.values(data.errors).flat().join('\n');
            resultMsg.className = 'pf-alert pf-alert-danger';
            resultMsg.textContent = 'Error: ' + msg;
        } else {
            resultMsg.className = 'pf-alert pf-alert-success';
            resultMsg.innerHTML = 'Task launched! ID: <strong>' + (data.task_id || '?') + '</strong>. <a href="{{ route("claude-worker.index") }}">View in Admin</a>';
        }
        resultDiv.style.display = '';
    } catch (err) {
        resultMsg.className = 'pf-alert pf-alert-danger';
        resultMsg.textContent = 'Network error: ' + err.message;
        resultDiv.style.display = '';
    }
}
</script>
@endsection
