<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_flow_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('wa_flows')->cascadeOnDelete();
            $table->string('type'); // trigger, message, image, button, condition, ai, wait, action
            $table->string('label');
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->json('config')->nullable(); // flexible config per node type
            $table->text('reply_message')->nullable();
            $table->string('media_url')->nullable();
            $table->foreignId('ai_key_id')->nullable()->constrained('wa_ai_keys')->nullOnDelete();
            $table->string('condition_field')->nullable();
            $table->string('condition_operator')->nullable(); // equals, contains, not_contains, greater_than
            $table->string('condition_value')->nullable();
            $table->foreignId('next_node_id_true')->nullable()->constrained('wa_flow_nodes')->nullOnDelete();
            $table->foreignId('next_node_id_false')->nullable()->constrained('wa_flow_nodes')->nullOnDelete();
            $table->integer('wait_seconds')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_flow_nodes');
    }
};
