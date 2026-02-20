<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = ''): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasActiveSubscription()) {
            return redirect()->route('subscription.plans')
                ->with('warning', 'You need an active subscription to access this feature.');
        }

        if ($feature === 'file_upload') {
            $plan = $user->subscriptionPlan();
            if (!$plan || !$plan->can_upload_files) {
                return redirect()->route('dashboard')
                    ->with('warning', 'File uploads require a Professional or Enterprise plan.');
            }
        }

        return $next($request);
    }
}
