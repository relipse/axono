<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SocialAccount;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()->socialAccounts()->latest()->get();
        return view('social-accounts.index', compact('accounts'));
    }

    public function create(Request $request)
    {
        if (!$request->user()->canAddSocialAccount()) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'You have reached your social account limit. Upgrade your plan to add more.');
        }

        return view('social-accounts.create');
    }

    public function store(Request $request)
    {
        if (!$request->user()->canAddSocialAccount()) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'You have reached your social account limit.');
        }

        $validated = $request->validate([
            'platform' => ['required', 'in:twitter,facebook,linkedin,instagram'],
            'username' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'access_token' => ['required', 'string'],
            'refresh_token' => ['nullable', 'string'],
            'platform_user_id' => ['nullable', 'string', 'max:255'],
        ]);

        $account = $request->user()->socialAccounts()->create($validated);

        ActivityLog::log($request->user(), 'social_account.created', $account);

        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account connected successfully.');
    }

    public function edit(SocialAccount $socialAccount)
    {
        $this->authorize('update', $socialAccount);
        return view('social-accounts.edit', compact('socialAccount'));
    }

    public function update(Request $request, SocialAccount $socialAccount)
    {
        $this->authorize('update', $socialAccount);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'access_token' => ['nullable', 'string'],
            'refresh_token' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if (empty($validated['access_token'])) {
            unset($validated['access_token']);
        }
        if (empty($validated['refresh_token'])) {
            unset($validated['refresh_token']);
        }

        $socialAccount->update($validated);

        ActivityLog::log($request->user(), 'social_account.updated', $socialAccount);

        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account updated.');
    }

    public function destroy(Request $request, SocialAccount $socialAccount)
    {
        $this->authorize('delete', $socialAccount);

        ActivityLog::log($request->user(), 'social_account.deleted', $socialAccount, [
            'platform' => $socialAccount->platform,
            'username' => $socialAccount->username,
        ]);

        $socialAccount->delete();

        return redirect()->route('social-accounts.index')
            ->with('success', 'Social account removed.');
    }
}
