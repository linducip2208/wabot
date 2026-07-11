<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_post_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable()->default('#3b82f6');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_post_campaigns');
    }
};
