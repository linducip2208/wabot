<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('billing_period')->default('monthly');
            $table->json('features')->nullable();
            $table->integer('max_sessions')->default(1);
            $table->integer('max_contacts')->default(100);
            $table->integer('max_autoreplies')->default(10);
            $table->integer('max_campaign_recipients')->default(50);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('id')->constrained('plans')->nullOnDelete();
            $table->timestamp('trial_ends_at')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
            $table->dropColumn('trial_ends_at');
        });
        Schema::dropIfExists('plans');
    }
};
