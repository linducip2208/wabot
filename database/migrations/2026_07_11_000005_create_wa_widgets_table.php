<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('greeting_message')->nullable();
            $table->text('offline_message')->nullable();
            $table->string('theme_color')->default('#6366f1');
            $table->string('position')->default('bottom-right');
            $table->string('button_icon')->default('chat');
            $table->json('channels')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('embed_key', 64)->unique();
            $table->timestamps();
        });

        Schema::create('wa_widget_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_widget_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('message')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_widget_leads');
        Schema::dropIfExists('wa_widgets');
    }
};
