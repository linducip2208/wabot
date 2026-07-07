<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_click_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('wa_campaigns')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('wa_contacts')->cascadeOnDelete();
            $table->string('token', 64)->nullable()->unique();
            $table->string('link_url');
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_click_events');
    }
};
