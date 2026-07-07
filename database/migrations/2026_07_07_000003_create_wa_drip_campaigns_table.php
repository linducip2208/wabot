<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_drip_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('wa_sessions')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->boolean('send_to_new_only')->default(true); // only new contacts
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_drip_campaigns');
    }
};
