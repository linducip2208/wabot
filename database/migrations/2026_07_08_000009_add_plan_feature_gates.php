<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('can_use_meta')->default(false)->after('can_manage_server');
            $table->boolean('can_use_forms')->default(false)->after('can_use_meta');
            $table->boolean('can_use_calling')->default(false)->after('can_use_forms');
            $table->boolean('can_use_instagram')->default(false)->after('can_use_calling');
            $table->boolean('can_use_flow')->default(false)->after('can_use_instagram');
            $table->boolean('can_use_ai_agent')->default(false)->after('can_use_flow');
            $table->boolean('can_use_intent')->default(false)->after('can_use_ai_agent');
            $table->boolean('can_use_drip')->default(false)->after('can_use_intent');
            $table->boolean('can_use_ab_test')->default(false)->after('can_use_drip');
            $table->boolean('can_use_catalog')->default(false)->after('can_use_ab_test');
            $table->boolean('can_use_commerce')->default(false)->after('can_use_catalog');
            $table->boolean('can_use_deals')->default(false)->after('can_use_commerce');
            $table->boolean('can_use_kanban')->default(false)->after('can_use_deals');
            $table->integer('max_meta_accounts')->default(0)->after('can_use_kanban');
            $table->integer('max_forms')->default(0)->after('max_meta_accounts');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'can_use_meta', 'can_use_forms', 'can_use_calling', 'can_use_instagram',
                'can_use_flow', 'can_use_ai_agent', 'can_use_intent', 'can_use_drip',
                'can_use_ab_test', 'can_use_catalog', 'can_use_commerce',
                'can_use_deals', 'can_use_kanban', 'max_meta_accounts', 'max_forms',
            ]);
        });
    }
};
