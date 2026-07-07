<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_sla_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->foreignId('sla_config_id')->nullable()->constrained('wa_sla_configs')->nullOnDelete();
            $table->foreignId('team_member_id')->nullable()->constrained('wa_team_members')->nullOnDelete();
            $table->timestamp('customer_message_at');
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('first_response_breached')->default(false);
            $table->boolean('resolution_breached')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_sla_logs');
    }
};
