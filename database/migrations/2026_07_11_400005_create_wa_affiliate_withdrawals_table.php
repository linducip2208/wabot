<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30);
            $table->text('payment_details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_affiliate_withdrawals');
    }
};
