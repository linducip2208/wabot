<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_commerce_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('wa_commerce_orders')->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->nullable()->constrained('wa_catalog_items')->nullOnDelete();
            $table->string('name');
            $table->integer('qty')->default(1);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_commerce_items');
    }
};
