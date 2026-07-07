<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_conversation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('wa_contacts')->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained('wa_team_members')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('wa_sessions')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->string('status')->default('active'); // active, closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_conversation_assignments');
    }
};
