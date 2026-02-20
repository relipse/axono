<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign free trial on the Starter plan
        $starterPlan = SubscriptionPlan::where('slug', 'starter')->first();
        if ($starterPlan) {
            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $starterPlan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'trial_ends_at' => now()->addDays(14),
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
