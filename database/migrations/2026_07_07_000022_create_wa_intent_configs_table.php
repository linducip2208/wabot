<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_intent_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('intent_label'); // beli, komplain, tanya_harga, support, dll
            $table->text('keywords'); // kata kunci dipisah koma
            $table->foreignId('ai_key_id')->nullable()->constrained('wa_ai_keys')->nullOnDelete();
            $table->text('auto_reply')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_intent_configs');
    }
};
