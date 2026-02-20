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

        $connectedPlatforms = $request->user()->socialAccounts()
            ->pluck('platform')
            ->toArray();

        return view('social-accounts.create', compact('connectedPlatforms'));
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
            'display_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

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
