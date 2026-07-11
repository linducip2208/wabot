<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('driver')->nullable()->after('code')->comment('manual, stripe, razorpay');
            $table->text('api_key')->nullable()->after('instructions')->comment('encrypted: stripe secret_key or razorpay key_id');
            $table->text('api_secret')->nullable()->after('api_key')->comment('encrypted: stripe webhook_secret or razorpay key_secret');
            $table->boolean('is_auto')->default(false)->after('is_active')->comment('auto-capture enabled');
            $table->json('meta')->nullable()->after('api_secret')->comment('driver-specific config');
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->dropColumn(['driver', 'api_key', 'api_secret', 'is_auto', 'meta']);
        });
    }
};
