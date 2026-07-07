<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_ai_keys', function (Blueprint $table) {
            $table->string('base_url')->nullable()->after('model');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->unsignedBigInteger('author_id')->nullable()->change();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('wa_session_logs', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->unsignedBigInteger('session_id')->nullable()->change();
            $table->foreign('session_id')->references('id')->on('wa_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wa_session_logs', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->unsignedBigInteger('session_id')->nullable(false)->change();
            $table->foreign('session_id')->references('id')->on('wa_sessions')->cascadeOnDelete();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->unsignedBigInteger('author_id')->nullable(false)->change();
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('wa_ai_keys', function (Blueprint $table) {
            $table->dropColumn('base_url');
        });
    }
};
