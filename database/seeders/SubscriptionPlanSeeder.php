<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for individuals getting started with social media automation.',
                'price' => 9.99,
                'billing_cycle' => 'monthly',
                'max_social_accounts' => 2,
                'max_posts_per_day' => 5,
                'max_scheduled_posts' => 25,
                'can_upload_files' => false,
                'max_file_size_mb' => 0,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For professionals managing multiple social accounts with file-based scheduling.',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'max_social_accounts' => 5,
                'max_posts_per_day' => 20,
                'max_scheduled_posts' => 100,
                'can_upload_files' => true,
                'max_file_size_mb' => 5,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited power for agencies and large teams with bulk scheduling.',
                'price' => 79.99,
                'billing_cycle' => 'monthly',
                'max_social_accounts' => 20,
                'max_posts_per_day' => 100,
                'max_scheduled_posts' => 500,
                'can_upload_files' => true,
                'max_file_size_mb' => 25,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
