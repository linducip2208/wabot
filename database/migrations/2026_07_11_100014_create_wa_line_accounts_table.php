<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_line_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel_id')->nullable();
            $table->text('channel_secret')->nullable();
            $table->text('channel_access_token')->nullable();
            $table->string('bot_basic_id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('picture_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->dateTime('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_line_accounts');
    }
};
