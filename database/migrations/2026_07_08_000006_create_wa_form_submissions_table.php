<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('wa_forms')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('wa_contacts')->nullOnDelete();
            $table->string('phone')->nullable();
            $table->json('data')->comment('Submitted form data as key-value pairs');
            $table->string('message_id')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_form_submissions');
    }
};
