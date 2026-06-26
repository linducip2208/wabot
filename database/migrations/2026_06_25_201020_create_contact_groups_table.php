<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_group_pivot', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('contact_groups')->cascadeOnDelete();
            $table->primary(['contact_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_group_pivot');
        Schema::dropIfExists('contact_groups');
    }
};
