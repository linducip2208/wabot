<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fkColumns = [
            ['column' => 'facebook_account_id', 'table' => 'wa_facebook_accounts'],
            ['column' => 'instagram_account_id', 'table' => 'wa_instagram_accounts'],
            ['column' => 'gbm_account_id', 'table' => 'wa_gbm_accounts'],
            ['column' => 'discord_account_id', 'table' => 'wa_discord_accounts'],
            ['column' => 'tiktok_account_id', 'table' => 'wa_tiktok_accounts'],
            ['column' => 'line_account_id', 'table' => 'wa_line_accounts'],
            ['column' => 'twitter_account_id', 'table' => 'wa_twitter_accounts'],
            ['column' => 'twilio_account_id', 'table' => 'wa_twilio_accounts'],
            ['column' => 'sendgrid_account_id', 'table' => 'wa_sendgrid_accounts'],
        ];

        foreach (['wa_campaigns', 'wa_recurrings'] as $tableName) {
            foreach ($fkColumns as $fk) {
                if (!Schema::hasColumn($tableName, $fk['column'])) {
                    Schema::table($tableName, function (Blueprint $table) use ($fk) {
                        $table->foreignId($fk['column'])
                            ->nullable()
                            ->after('telegram_account_id')
                            ->constrained($fk['table'])
                            ->nullOnDelete();
                    });
                }
            }
        }
    }

    public function down(): void
    {
        $fkColumns = [
            'facebook_account_id',
            'instagram_account_id',
            'gbm_account_id',
            'discord_account_id',
            'tiktok_account_id',
            'line_account_id',
            'twitter_account_id',
            'twilio_account_id',
            'sendgrid_account_id',
        ];

        foreach (['wa_campaigns', 'wa_recurrings'] as $tableName) {
            foreach ($fkColumns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    Schema::table($tableName, function (Blueprint $table) use ($column) {
                        $table->dropForeign([$column]);
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
