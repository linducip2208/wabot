<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->cascadeOnDelete();
            $table->string('discount_type', 20)->default('percentage');
            $table->decimal('discount_value', 12, 2);
            $table->decimal('min_order', 12, 2)->default(0);
            $table->integer('max_uses')->default(0);
            $table->integer('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_coupons');
    }
};
