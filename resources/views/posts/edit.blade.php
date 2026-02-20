@extends('layouts.app')
@section('title', 'Edit Post')

@section('content')
<div class="pf-max-w-3xl">
    <div class="pf-page-header">
        <h1>Edit Post</h1>
        <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-secondary pf-btn-sm">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('posts.update', $post) }}">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            {{-- Left Column: Form --}}
            <div>
                <div class="pf-card">
                    <div class="pf-card-body">
                        <div class="pf-form-group">
                            <label for="social_account_id" class="pf-label">Social Account</label>
                            <select id="social_account_id" name="social_account_id" required class="pf-select"
                                data-limits='@json($platformLimits)'>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}"
                                        data-platform="{{ $account->platform }}"
                                        {{ old('social_account_id', $post->social_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->platformLabel() }} - {{ $account->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pf-form-group">
                            <label for="content" class="pf-label">Content</label>
                            <textarea id="content" name="content" rows="6" required class="pf-textarea">{{ old('content', $post->content) }}</textarea>
                            <div class="pf-char-counter" id="charCounter">
                                <div class="pf-char-bar"><div class="pf-char-bar-fill" id="charBarFill"></div></div>
                                <span class="pf-char-text" id="charText">0 / 280</span>
                            </div>
                        </div>

                        <div class="pf-form-group">
                            <label for="scheduled_at" class="pf-label">Schedule For (optional)</label>
                            <input id="scheduled_at" type="datetime-local" name="scheduled_at"
                                value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}" class="pf-input">
                            <p class="pf-hint">Leave empty to save as draft</p>
                        </div>

                        @if($post->error_message)
                            <div class="pf-alert pf-alert-danger">
                                <strong>Last Error:</strong> {{ $post->error_message }}
                            </div>
                        @endif

                        <div class="pf-flex pf-gap-3" style="justify-content: flex-end;">
                            <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                            <button type="submit" class="pf-btn pf-btn-primary">Update Post</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Live Preview --}}
            <div>
                <div class="pf-card">
                    <div class="pf-card-header">
                        <h3>Preview</h3>
                    </div>
                    <div class="pf-card-body" id="previewArea"></div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentEl = document.getElementById('content');
    const selectEl = document.getElementById('social_account_id');
    const previewArea = document.getElementById('previewArea');
    const charBarFill = document.getElementById('charBarFill');
    const charText = document.getElementById('charText');
    const limits = JSON.parse(selectEl.dataset.limits);

    const platformLabels = {
        twitter: 'Twitter / X', facebook: 'Facebook',
        linkedin: 'LinkedIn', instagram: 'Instagram'
    };

    function getSelectedPlatform() {
        const opt = selectEl.options[selectEl.selectedIndex];
        return { platform: opt.dataset.platform, limit: limits[opt.dataset.platform] || 5000 };
    }

    function updatePreview() {
        const content = contentEl.value;
        const p = getSelectedPlatform();
        const len = content.length;
        const pct = Math.min((len / p.limit) * 100, 100);
        const isOver = len > p.limit;
        const remaining = p.limit - len;

        charText.textContent = len + ' / ' + p.limit.toLocaleString();
        charBarFill.style.width = pct + '%';
        charBarFill.style.background = pct > 90 ? 'var(--pf-danger-500)' : pct > 70 ? 'var(--pf-warning-500)' : 'var(--pf-success-500)';

        let badgeClass = isOver ? 'pf-badge-red' : remaining < p.limit * 0.1 ? 'pf-badge-yellow' : 'pf-badge-green';
        let badgeText = isOver ? Math.abs(remaining).toLocaleString() + ' over limit' : remaining.toLocaleString() + ' remaining';
        const displayContent = isOver ? content.substring(0, p.limit) : content;

        const div = document.createElement('div');
        div.textContent = displayContent || 'Start typing...';
        const escaped = div.innerHTML;

        previewArea.innerHTML = `
            <div class="pf-preview-card">
                <div class="pf-preview-header">
                    <div class="pf-platform-icon ${p.platform}" style="width: 1.5rem; height: 1.5rem; font-size: 0.625rem; border-radius: 4px;">
                        ${p.platform.substring(0, 2).toUpperCase()}
                    </div>
                    <span class="platform-name">${platformLabels[p.platform] || p.platform}</span>
                    <span class="char-limit pf-badge ${badgeClass}">${badgeText}</span>
                </div>
                <div class="pf-preview-body ${isOver ? 'truncated' : ''}">
                    ${escaped}${isOver ? '<span style="color: var(--pf-danger-500); font-weight: 600;">...</span>' : ''}
                </div>
                <div class="pf-preview-footer">
                    <span>${p.limit.toLocaleString()} character limit</span>
                    <span>${len.toLocaleString()} / ${p.limit.toLocaleString()}</span>
                </div>
            </div>`;
    }

    contentEl.addEventListener('input', updatePreview);
    selectEl.addEventListener('change', updatePreview);
    updatePreview();
});
</script>
@endsection
