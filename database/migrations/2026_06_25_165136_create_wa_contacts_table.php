<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_contacts');
    }
};
