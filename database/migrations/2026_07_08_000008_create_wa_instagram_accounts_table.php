<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_instagram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('instagram_id')->nullable();
            $table->string('username')->nullable();
            $table->text('access_token')->nullable();
            $table->string('app_id')->nullable();
            $table->string('app_secret')->nullable();
            $table->string('webhook_verify_token')->nullable();
            $table->string('status')->default('disconnected');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_instagram_accounts');
    }
};
