<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_ai_image_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('prompt');
            $table->string('style')->default('photorealistic');
            $table->string('size')->default('square');
            $table->integer('count')->default(1);
            $table->string('status')->default('pending');
            $table->json('results')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_ai_image_jobs');
    }
};
