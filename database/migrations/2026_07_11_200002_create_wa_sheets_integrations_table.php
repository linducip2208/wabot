<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_sheets_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('spreadsheet_id');
            $table->string('sheet_name')->default('Sheet1');
            $table->text('service_account_json')->comment('encrypted');
            $table->string('sync_direction')->default('import'); // import, export, both
            $table->boolean('is_active')->default(false);
            $table->string('sync_status')->default('never')->comment('never, syncing, synced, failed');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_sheets_integrations');
    }
};
