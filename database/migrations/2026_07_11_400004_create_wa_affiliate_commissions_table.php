<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('rate', 5, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_affiliate_commissions');
    }
};
