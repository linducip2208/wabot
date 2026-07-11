<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_facebook_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('page_id')->comment('Facebook Page ID');
            $table->string('page_name')->nullable();
            $table->text('page_token_encrypted')->nullable();
            $table->text('app_secret_encrypted')->nullable();
            $table->string('status')->default('disconnected');
            $table->boolean('is_active')->default(true);
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_facebook_accounts');
    }
};
