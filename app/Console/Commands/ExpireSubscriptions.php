<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Tandai subscription yang sudah melewati ends_at atau expires_at sebagai expired';

    public function handle(): int
    {
        $count = 0;

        Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->each(function ($sub) use (&$count) {
                $sub->update(['status' => 'expired']);
                if ($sub->user && $sub->user->plan_id === $sub->plan_id) {
                    $sub->user->update(['plan_id' => null]);
                }
                $count++;
            });

        User::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereNotNull('plan_id')
            ->each(function ($user) use (&$count) {
                $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);
                $user->update(['plan_id' => null]);
                $count++;
            });

        $this->info("Expired: {$count} subscriptions/users.");
        return 0;
    }
}
