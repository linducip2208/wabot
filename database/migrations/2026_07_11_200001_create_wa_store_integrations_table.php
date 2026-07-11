<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_store_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('platform'); // woocommerce, shopify
            $table->string('name');
            $table->string('base_url');
            $table->text('api_key')->nullable()->comment('encrypted');
            $table->text('api_secret')->nullable()->comment('encrypted');
            $table->text('webhook_secret')->nullable()->comment('encrypted: shared secret for HMAC verification');
            $table->boolean('is_active')->default(false);
            $table->string('sync_status')->default('never')->comment('never, syncing, synced, failed');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('settings')->nullable()->comment('order_template_id, tracking_template_id, auto_reply_keywords');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_store_integrations');
    }
};
