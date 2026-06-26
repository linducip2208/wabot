<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::upsert([
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'billing_period' => 'monthly',
                'features' => json_encode(['Auto-Reply Keyword']),
                'max_sessions' => 1,
                'max_contacts' => 100,
                'max_autoreplies' => 10,
                'max_campaign_recipients' => 50,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'price' => 150000,
                'billing_period' => 'monthly',
                'features' => json_encode(['Auto-Reply Keyword', 'Import CSV', 'API Access', 'Webhook']),
                'max_sessions' => 3,
                'max_contacts' => 5000,
                'max_autoreplies' => 50,
                'max_campaign_recipients' => 1000,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 500000,
                'billing_period' => 'monthly',
                'features' => json_encode(['Auto-Reply Keyword', 'Import CSV', 'API Access', 'Webhook', 'Whitelabel', 'Reseller', 'Priority Support']),
                'max_sessions' => 10,
                'max_contacts' => 50000,
                'max_autoreplies' => 200,
                'max_campaign_recipients' => 10000,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ], ['slug'], [
            'name', 'price', 'features', 'max_sessions', 'max_contacts',
            'max_autoreplies', 'max_campaign_recipients',
        ]);
    }
}
