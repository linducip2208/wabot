<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_discord_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('bot_token');
            $table->string('bot_name')->nullable();
            $table->string('guild_id')->nullable();
            $table->string('application_id')->nullable();
            $table->string('public_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_discord_accounts');
    }
};
