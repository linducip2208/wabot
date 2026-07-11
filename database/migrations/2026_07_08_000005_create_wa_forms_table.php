<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meta_account_id')->nullable()->constrained('wa_meta_accounts')->nullOnDelete();
            $table->string('name');
            $table->string('header_text')->nullable();
            $table->text('body_text')->nullable();
            $table->json('components')->comment('Form components: [{type, label, required, options, placeholder}]');
            $table->string('status')->default('draft');
            $table->integer('submission_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_forms');
    }
};
