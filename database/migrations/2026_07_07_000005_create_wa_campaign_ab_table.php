<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_campaign_ab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('wa_sessions')->nullOnDelete();
            $table->string('name');
            $table->text('variant_a_message');
            $table->text('variant_b_message');
            $table->string('media_url_a')->nullable();
            $table->string('media_url_b')->nullable();
            $table->integer('a_sent')->default(0);
            $table->integer('a_replied')->default(0);
            $table->integer('b_sent')->default(0);
            $table->integer('b_replied')->default(0);
            $table->string('winner')->nullable(); // a or b
            $table->boolean('is_active')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_campaign_ab');
    }
};
