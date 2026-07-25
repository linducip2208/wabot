<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wa_campaigns MODIFY COLUMN status VARCHAR(20) DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wa_campaigns MODIFY COLUMN status ENUM('draft', 'sending', 'sent', 'failed') DEFAULT 'draft'");
    }
};
