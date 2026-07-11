<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_rss_schedule_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rss_schedule_id')->constrained('wa_rss_schedules')->cascadeOnDelete();
            $table->string('post_url');
            $table->string('content_hash', 64);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['rss_schedule_id', 'content_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_rss_schedule_histories');
    }
};
