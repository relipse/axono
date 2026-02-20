<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private array $platformDrivers = [
        'twitter' => 'twitter',
        'facebook' => 'facebook',
        'linkedin' => 'linkedin-openid',
    ];

    private array $platformScopes = [
        'twitter' => ['tweet.read', 'tweet.write', 'users.read', 'offline.access'],
        'facebook' => ['pages_manage_posts', 'pages_read_engagement'],
        'linkedin' => ['w_member_social', 'r_liteprofile'],
    ];

    public function redirect(Request $request, string $platform)
    {
        if (!$this->isValidPlatform($platform)) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'Unsupported platform.');
        }

        if (!$request->user()->canAddSocialAccount()) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'You have reached your social account limit. Upgrade your plan to add more.');
        }

        $driver = Socialite::driver($this->platformDrivers[$platform]);

        if (isset($this->platformScopes[$platform])) {
            $driver->scopes($this->platformScopes[$platform]);
        }

        // Store platform in session so callback knows which platform
        session(['oauth_platform' => $platform]);

        return $driver->redirect();
    }

    public function callback(Request $request, string $platform)
    {
        if (!$this->isValidPlatform($platform)) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'Unsupported platform.');
        }

        try {
            $socialUser = Socialite::driver($this->platformDrivers[$platform])->user();
        } catch (\Exception $e) {
            Log::error("OAuth callback error for {$platform}", ['error' => $e->getMessage()]);
            return redirect()->route('social-accounts.index')
                ->with('error', 'Failed to connect. Please try again.');
        }

        $user = $request->user();

        // Check if this platform account is already connected
        $existing = $user->socialAccounts()
            ->where('platform', $platform)
            ->where('platform_user_id', $socialUser->getId())
            ->first();

        if ($existing) {
            // Update tokens on the existing account
            $existing->update([
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
                'username' => $socialUser->getNickname() ?? $socialUser->getName(),
                'display_name' => $socialUser->getName(),
                'is_active' => true,
            ]);

            ActivityLog::log($user, 'social_account.reconnected', $existing);

            return redirect()->route('social-accounts.index')
                ->with('success', 'Account reconnected and tokens refreshed.');
        }

        // Enforce account limit again (in case it changed)
        if (!$user->canAddSocialAccount()) {
            return redirect()->route('social-accounts.index')
                ->with('warning', 'You have reached your social account limit.');
        }

        $account = SocialAccount::create([
            'user_id' => $user->id,
            'platform' => $platform,
            'platform_user_id' => $socialUser->getId(),
            'username' => $socialUser->getNickname() ?? $socialUser->getName(),
            'display_name' => $socialUser->getName(),
            'access_token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
        ]);

        ActivityLog::log($user, 'social_account.created', $account);

        return redirect()->route('social-accounts.index')
            ->with('success', ucfirst($platform) . ' account connected successfully.');
    }

    private function isValidPlatform(string $platform): bool
    {
        return array_key_exists($platform, $this->platformDrivers);
    }
}
