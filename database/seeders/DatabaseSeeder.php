<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(PlanSeeder::class);
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@wabot.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_id' => $adminRole?->id,
                'plan_id' => 3,
            ]
        );

        if (!$admin->activeSubscription()) {
            Subscription::create([
                'user_id' => $admin->id,
                'plan_id' => 3,
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }
    }
}
