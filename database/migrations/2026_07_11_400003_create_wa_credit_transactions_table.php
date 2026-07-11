<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);
            $table->integer('amount');
            $table->integer('balance_after');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_credit_transactions');
    }
};
