<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->json('media_urls')->nullable();
            $table->json('platform_targets')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('draft');
            $table->json('result')->nullable();
            $table->foreignId('campaign_id')->nullable()->constrained('wa_post_campaigns')->nullOnDelete();
            $table->foreignId('label_id')->nullable()->constrained('wa_post_labels')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('scheduled_at');
            $table->index('campaign_id');
            $table->index('label_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_posts');
    }
};
