<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('wa_deal_stages')->restrictOnDelete();
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('status')->default('open'); // open, won, lost
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_deals');
    }
};
