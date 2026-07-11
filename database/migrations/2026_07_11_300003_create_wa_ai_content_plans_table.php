<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_ai_content_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->json('platforms');
            $table->json('topics')->nullable();
            $table->string('frequency')->default('weekly');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft');
            $table->json('calendar_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_ai_content_plans');
    }
};
