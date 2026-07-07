<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_interactive_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('header_type')->default('text'); // text, image, video, document
            $table->text('header_text')->nullable();
            $table->string('header_media_url')->nullable();
            $table->text('body_text');
            $table->text('footer_text')->nullable();
            $table->json('buttons'); // [{id, text}]
            $table->foreignId('session_id')->nullable()->constrained('wa_sessions')->nullOnDelete();
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_interactive_buttons');
    }
};
