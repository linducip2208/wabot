<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $allPermissions = [
            'whatsapp_send' => true,
            'whatsapp_bulk' => true,
            'whatsapp_campaign' => true,
            'autoreply' => true,
            'recurring' => true,
            'contacts' => true,
            'contact_groups' => true,
            'templates' => true,
            'webhooks' => true,
            'api_tokens' => true,
            'ai_keys' => true,
            'chat' => true,
            'reports' => true,
            'vouchers_redeem' => true,
        ];

        $basicPermissions = [
            'whatsapp_send' => true,
            'whatsapp_bulk' => false,
            'whatsapp_campaign' => false,
            'autoreply' => false,
            'recurring' => false,
            'contacts' => true,
            'contact_groups' => false,
            'templates' => true,
            'webhooks' => false,
            'api_tokens' => false,
            'ai_keys' => false,
            'chat' => true,
            'reports' => false,
            'vouchers_redeem' => false,
        ];

        Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'is_protected' => true,
                'permissions' => $allPermissions,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'user'],
            [
                'is_protected' => true,
                'permissions' => $basicPermissions,
            ]
        );
    }
}
