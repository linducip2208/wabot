<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_call_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meta_account_id')->nullable()->constrained('wa_meta_accounts')->nullOnDelete();
            $table->string('name');
            $table->text('message')->comment('Text to convert to speech');
            $table->string('voice_id')->nullable()->comment('ElevenLabs voice ID');
            $table->json('recipient_ids');
            $table->string('status')->default('draft');
            $table->integer('total_recipients')->default(0);
            $table->integer('called_count')->default(0);
            $table->integer('answered_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('delay_seconds')->default(10);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wa_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_id')->nullable()->constrained('wa_call_broadcasts')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('wa_contacts')->nullOnDelete();
            $table->foreignId('meta_account_id')->nullable()->constrained('wa_meta_accounts')->nullOnDelete();
            $table->string('phone');
            $table->string('status')->default('pending');
            $table->integer('duration_seconds')->nullable();
            $table->string('call_id')->nullable();
            $table->text('audio_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_call_logs');
        Schema::dropIfExists('wa_call_broadcasts');
    }
};
