<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price')->get();
        $currentSubscription = $request->user()?->subscription;
        return view('subscription.plans', compact('plans', 'currentSubscription'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $user = $request->user();

        // Cancel existing subscription
        $existing = $user->subscription;
        if ($existing) {
            $existing->update(['status' => 'cancelled', 'ends_at' => now()]);
        }

        // In a real app, integrate payment gateway here (Stripe, etc.)
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        ActivityLog::log($user, 'subscription.activated', $subscription, [
            'plan' => $plan->name,
        ]);

        return redirect()->route('dashboard')
            ->with('success', "Subscribed to {$plan->name} plan!");
    }

    public function cancel(Request $request)
    {
        $subscription = $request->user()->subscription;

        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);

            ActivityLog::log($request->user(), 'subscription.cancelled', $subscription);
        }

        return redirect()->route('subscription.plans')
            ->with('success', 'Subscription cancelled.');
    }
}
