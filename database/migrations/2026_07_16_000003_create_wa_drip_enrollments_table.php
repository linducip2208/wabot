<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_campaign_id')->constrained('wa_drip_campaigns')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->integer('current_step')->default(0);
            $table->timestamp('next_send_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['drip_campaign_id', 'contact_id']);
        });

        Schema::table('wa_drip_campaigns', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('send_to_new_only');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_drip_enrollments');

        Schema::table('wa_drip_campaigns', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
