<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_drip_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('wa_drip_campaigns')->cascadeOnDelete();
            $table->integer('step_order');
            $table->integer('wait_hours'); // hours to wait after previous step
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->foreignId('ai_key_id')->nullable()->constrained('wa_ai_keys')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_drip_steps');
    }
};
