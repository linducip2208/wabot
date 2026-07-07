<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_commerce_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('wa_sessions')->nullOnDelete();
            $table->string('order_number')->unique();
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, confirmed, paid, shipped, completed, cancelled
            $table->string('payment_method')->nullable();
            $table->string('payment_proof_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_commerce_orders');
    }
};
