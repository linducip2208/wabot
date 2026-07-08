<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `wa_autoreplies` MODIFY `match_type` ENUM('exact', 'contains', 'starts_with', 'welcome', 'fallback') NOT NULL DEFAULT 'contains'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `wa_autoreplies` MODIFY `match_type` ENUM('exact', 'contains', 'starts_with') NOT NULL DEFAULT 'contains'");
    }
};
