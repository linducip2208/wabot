<?php

namespace App\Console\Commands;

use App\Models\WaSession;
use Illuminate\Console\Command;

class CleanupOldSessions extends Command
{
    protected $signature = 'wabot:cleanup-sessions {--days=30 : Delete sessions inactive for N days}';
    protected $description = 'Delete disconnected sessions older than N days';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $sessions = WaSession::where('status', 'disconnected')
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($sessions as $session) {
            $session->delete();
        }

        $this->info("Cleaned up {$sessions->count()} old sessions.");
    }
}
