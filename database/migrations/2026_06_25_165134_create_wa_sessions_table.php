<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('wa_servers')->nullOnDelete();
            $table->string('session_id')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'qr_ready', 'connecting', 'connected', 'disconnected', 'error'])
                ->default('pending');
            $table->text('qr_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_sessions');
    }
};
