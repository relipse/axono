@extends('layouts.app')
@section('title', 'Create Post')

@section('content')
<div class="pf-max-w-3xl">
    <div class="pf-page-header">
        <h1>Create Post</h1>
        <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-secondary pf-btn-sm">&larr; Back</a>
    </div>

    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            {{-- Left Column: Form --}}
            <div>
                <div class="pf-card">
                    <div class="pf-card-body">
                        {{-- Platform Selection --}}
                        <div class="pf-form-group">
                            <label class="pf-label">Post To</label>
                            @if($accounts->isEmpty())
                                <div class="pf-alert pf-alert-warning">
                                    No social accounts connected. <a href="{{ route('social-accounts.create') }}">Connect one first</a>.
                                </div>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    @foreach($accounts as $account)
                                        <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.875rem; border: 1.5px solid var(--pf-gray-200); border-radius: var(--pf-radius-md); cursor: pointer; transition: all 0.15s;"
                                               class="platform-option"
                                               data-platform="{{ $account->platform }}"
                                               data-limit="{{ $platformLimits[$account->platform] ?? 5000 }}"
                                               data-account-id="{{ $account->id }}">
                                            <input type="checkbox" name="social_account_ids[]" value="{{ $account->id }}" class="pf-checkbox platform-checkbox"
                                                {{ is_array(old('social_account_ids')) && in_array($account->id, old('social_account_ids')) ? 'checked' : '' }}>
                                            <div class="pf-platform-icon {{ $account->platform }}" style="width: 2rem; height: 2rem; font-size: 0.75rem;">
                                                {{ strtoupper(substr($account->platform, 0, 2)) }}
                                            </div>
                                            <div style="flex: 1;">
                                                <div class="pf-text-sm pf-font-semibold">{{ $account->platformLabel() }}</div>
                                                <div class="pf-text-xs pf-text-muted">{{ '@' . $account->username }}</div>
                                            </div>
                                            <span class="pf-badge pf-badge-gray pf-text-xs">{{ number_format($platformLimits[$account->platform] ?? 5000) }} chars</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="pf-form-group">
                            <label for="content" class="pf-label">Content</label>
                            <textarea id="content" name="content" rows="6" required class="pf-textarea"
                                placeholder="What's on your mind?">{{ old('content') }}</textarea>
                            <div class="pf-char-counter" id="charCounter" style="display: none;">
                                <div class="pf-char-bar"><div class="pf-char-bar-fill" id="charBarFill"></div></div>
                                <span class="pf-char-text" id="charText">0 / 280</span>
                            </div>
                        </div>

                        {{-- Schedule --}}
                        <div class="pf-form-group">
                            <label for="scheduled_at" class="pf-label">Schedule For (optional)</label>
                            <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="pf-input">
                            <p class="pf-hint">Leave empty to save as draft</p>
                        </div>

                        {{-- Image --}}
                        <div class="pf-form-group">
                            <label for="image" class="pf-label">Image (optional)</label>
                            <input id="image" type="file" name="image" accept="image/*" class="pf-file-input">
                        </div>

                        <div class="pf-flex pf-gap-3" style="justify-content: flex-end;">
                            <a href="{{ route('posts.index') }}" class="pf-btn pf-btn-secondary">Cancel</a>
                            <button type="submit" class="pf-btn pf-btn-primary">Create Post</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Live Previews --}}
            <div>
                <div class="pf-card">
                    <div class="pf-card-header">
                        <h3>Platform Previews</h3>
                    </div>
                    <div class="pf-card-body" id="previewArea">
                        <p class="pf-text-sm pf-text-muted pf-text-center" id="previewPlaceholder">
                            Select platforms and type content to see previews
                        </p>
                        {{-- Previews inserted by JS --}}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.platform-option:has(input:checked) {
    border-color: var(--pf-primary-400);
    background: var(--pf-primary-50);
}
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
    const previewArea = document.getElementById('previewArea');
    const placeholder = document.getElementById('previewPlaceholder');
    const charCounter = document.getElementById('charCounter');
    const charBarFill = document.getElementById('charBarFill');
    const charText = document.getElementById('charText');
    const checkboxes = document.querySelectorAll('.platform-checkbox');

    const platformLabels = {
        twitter: 'Twitter / X',
        facebook: 'Facebook',
        linkedin: 'LinkedIn',
        instagram: 'Instagram'
    };

    const platformColors = {
        twitter: '#000000',
        facebook: '#1877f2',
        linkedin: '#0a66c2',
        instagram: '#e4405f'
    };

    function getSelectedPlatforms() {
        const selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const option = cb.closest('.platform-option');
                selected.push({
                    platform: option.dataset.platform,
                    limit: parseInt(option.dataset.limit),
                    id: option.dataset.accountId
                });
            }
        });
        return selected;
    }

    function updatePreviews() {
        const content = contentEl.value;
        const platforms = getSelectedPlatforms();

        // Remove old previews
        previewArea.querySelectorAll('.pf-preview-card').forEach(el => el.remove());

        if (platforms.length === 0) {
            placeholder.style.display = 'block';
            charCounter.style.display = 'none';
            return;
        }

        placeholder.style.display = 'none';

        // Show char counter for the strictest limit
        const minLimit = Math.min(...platforms.map(p => p.limit));
        const len = content.length;
        const pct = Math.min((len / minLimit) * 100, 100);

        charCounter.style.display = 'flex';
        charText.textContent = len + ' / ' + minLimit.toLocaleString();

        let barColor = 'var(--pf-success-500)';
        if (pct > 90) barColor = 'var(--pf-danger-500)';
        else if (pct > 70) barColor = 'var(--pf-warning-500)';

        charBarFill.style.width = pct + '%';
        charBarFill.style.background = barColor;

        // Create preview for each selected platform
        platforms.forEach(p => {
            const card = document.createElement('div');
            card.className = 'pf-preview-card';

            const isOver = content.length > p.limit;
            const displayContent = isOver ? content.substring(0, p.limit) : content;
            const remaining = p.limit - content.length;

            let badgeClass = 'pf-badge-green';
            let badgeText = remaining.toLocaleString() + ' remaining';
            if (isOver) {
                badgeClass = 'pf-badge-red';
                badgeText = Math.abs(remaining).toLocaleString() + ' over limit';
            } else if (remaining < p.limit * 0.1) {
                badgeClass = 'pf-badge-yellow';
            }

            card.innerHTML = `
                <div class="pf-preview-header">
                    <div class="pf-platform-icon ${p.platform}" style="width: 1.5rem; height: 1.5rem; font-size: 0.625rem; border-radius: 4px;">
                        ${p.platform.substring(0, 2).toUpperCase()}
                    </div>
                    <span class="platform-name">${platformLabels[p.platform] || p.platform}</span>
                    <span class="char-limit pf-badge ${badgeClass}">${badgeText}</span>
                </div>
                <div class="pf-preview-body ${isOver ? 'truncated' : ''}">
                    ${escapeHtml(displayContent || 'Start typing to see preview...')}${isOver ? '<span style="color: var(--pf-danger-500); font-weight: 600;">...</span>' : ''}
                </div>
                <div class="pf-preview-footer">
                    <span>${p.limit.toLocaleString()} character limit</span>
                    <span>${content.length.toLocaleString()} / ${p.limit.toLocaleString()}</span>
                </div>
            `;

            previewArea.appendChild(card);
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    contentEl.addEventListener('input', updatePreviews);
    checkboxes.forEach(cb => cb.addEventListener('change', updatePreviews));

    // Initial render
    updatePreviews();
});
</script>
@endsection
