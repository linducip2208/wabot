<?php

namespace App\Services;

use App\Models\WaContact;
use App\Models\WaDiscordAccount;
use App\Models\WaFacebookAccount;
use App\Models\WaGbmAccount;
use App\Models\WaInstagramAccount;
use App\Models\WaLineAccount;
use App\Models\WaMetaAccount;
use App\Models\WaSendGridAccount;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Models\WaTiktokAccount;
use App\Models\WaTwilioAccount;
use App\Models\WaTwitterAccount;

class ChannelRegistry
{
    protected static array $channels = [
        'whatsapp' => [
            'prefix' => null,
            'label' => 'WhatsApp',
            'model' => WaSession::class,
            'service' => BaileysService::class,
            'icon' => 'fab fa-whatsapp',
        ],
        'meta' => [
            'prefix' => null,
            'label' => 'Meta / Cloud API',
            'model' => WaMetaAccount::class,
            'service' => MetaApiService::class,
            'icon' => 'fab fa-meta',
        ],
        'instagram' => [
            'prefix' => 'ig:',
            'label' => 'Instagram',
            'model' => WaInstagramAccount::class,
            'service' => InstagramService::class,
            'icon' => 'fab fa-instagram',
        ],
        'telegram' => [
            'prefix' => 'tg:',
            'label' => 'Telegram',
            'model' => WaTelegramAccount::class,
            'service' => TelegramService::class,
            'icon' => 'fab fa-telegram',
        ],
        'facebook' => [
            'prefix' => 'fb:',
            'label' => 'Facebook / Messenger',
            'model' => WaFacebookAccount::class,
            'service' => FacebookService::class,
            'icon' => 'fab fa-facebook',
        ],
        'gbm' => [
            'prefix' => 'gbm:',
            'label' => 'Google Business Messages',
            'model' => WaGbmAccount::class,
            'service' => GbmService::class,
            'icon' => 'fab fa-google',
        ],
        'discord' => [
            'prefix' => 'dc:',
            'label' => 'Discord',
            'model' => WaDiscordAccount::class,
            'service' => DiscordService::class,
            'icon' => 'fab fa-discord',
        ],
        'tiktok' => [
            'prefix' => 'tt:',
            'label' => 'TikTok',
            'model' => WaTiktokAccount::class,
            'service' => TikTokService::class,
            'icon' => 'fab fa-tiktok',
        ],
        'line' => [
            'prefix' => 'line:',
            'label' => 'LINE',
            'model' => WaLineAccount::class,
            'service' => LineService::class,
            'icon' => 'fab fa-line',
        ],
        'twitter' => [
            'prefix' => 'x:',
            'label' => 'X / Twitter',
            'model' => WaTwitterAccount::class,
            'service' => TwitterService::class,
            'icon' => 'fab fa-x-twitter',
        ],
        'sms' => [
            'prefix' => 'sms:',
            'label' => 'SMS',
            'model' => WaTwilioAccount::class,
            'service' => TwilioService::class,
            'icon' => 'fas fa-sms',
        ],
        'email' => [
            'prefix' => 'email:',
            'label' => 'Email',
            'model' => WaSendGridAccount::class,
            'service' => SendGridService::class,
            'icon' => 'fas fa-envelope',
        ],
    ];

    public static function all(): array
    {
        return self::$channels;
    }

    public static function prefixes(): array
    {
        return array_filter(array_map(fn($c) => $c['prefix'], self::$channels));
    }

    public static function getByPrefix(?string $prefix): string
    {
        foreach (self::$channels as $channel => $config) {
            if ($config['prefix'] === $prefix) {
                return $channel;
            }
        }
        return 'whatsapp';
    }

    public static function getByPhone(string $phone): string
    {
        foreach (self::$channels as $channel => $config) {
            if ($config['prefix'] && str_starts_with($phone, $config['prefix'])) {
                return $channel;
            }
        }
        return 'whatsapp';
    }

    public static function detectChannel(WaContact $contact): string
    {
        return self::getByPhone($contact->phone);
    }

    public static function getLabel(string $channel): string
    {
        return self::$channels[$channel]['label'] ?? ucfirst($channel);
    }

    public static function getConfig(string $channel): ?array
    {
        return self::$channels[$channel] ?? null;
    }

    public static function getModelClass(string $channel): ?string
    {
        return self::$channels[$channel]['model'] ?? null;
    }

    public static function getServiceClass(string $channel): ?string
    {
        return self::$channels[$channel]['service'] ?? null;
    }

    public static function getIcon(string $channel): string
    {
        return self::$channels[$channel]['icon'] ?? 'fas fa-comment';
    }

    public static function list(): array
    {
        return array_keys(self::$channels);
    }

    public static function isWhatsAppVariant(string $channel): bool
    {
        return in_array($channel, ['whatsapp', 'meta', 'baileys']);
    }

    public static function channelPhonePatterns(): array
    {
        $patterns = [];
        foreach (self::$channels as $channel => $config) {
            if ($config['prefix']) {
                $patterns[] = $config['prefix'] . '%';
            }
        }
        return $patterns;
    }
}
