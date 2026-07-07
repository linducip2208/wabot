<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function index()
    {
        $demoAccounts = $this->demoAccounts();
        $menuGroups = $this->menuGroups();
        $tutorialPhases = $this->tutorialPhases();
        $features = $this->features();

        $seoMeta = [
            'title' => 'Dokumentasi — WABot WhatsApp Marketing SaaS',
            'description' => 'Dokumentasi lengkap WABot. Tutorial langkah demi langkah, daftar fitur, akun demo, dan struktur menu admin.',
            'canonical' => url('/docs'),
        ];

        return view('docs.index', compact(
            'demoAccounts', 'menuGroups', 'tutorialPhases', 'features', 'seoMeta'
        ));
    }

    protected function demoAccounts(): array
    {
        return [
            ['role' => 'Admin', 'email' => 'admin@wabot.test', 'password' => 'password', 'scope' => 'Akses penuh: server, user, voucher, transaksi, payout, CMS, blog'],
            ['role' => 'User', 'email' => 'user@wabot.test', 'password' => 'password', 'scope' => 'Akses standar: chat, kontak, kampanye, auto-reply, sesi, webhook'],
        ];
    }

    protected function menuGroups(): array
    {
        return [
            [
                'title' => 'Utama',
                'icon' => 'fa-star',
                'items' => [
                    ['icon' => 'fa-comments', 'label' => 'Chat Omni-Channel', 'desc' => 'Inbox percakapan real-time'],
                    ['icon' => 'fa-chart-pie', 'label' => 'Dashboard', 'desc' => 'Statistik & grafik aktivitas'],
                ]
            ],
            [
                'title' => 'WhatsApp',
                'icon' => 'fa-whatsapp',
                'items' => [
                    ['icon' => 'fa-mobile-alt', 'label' => 'Sesi / Agen', 'desc' => 'Kelola koneksi WhatsApp'],
                    ['icon' => 'fa-address-book', 'label' => 'Kontak', 'desc' => 'Database kontak & import CSV'],
                    ['icon' => 'fa-layer-group', 'label' => 'Grup Kontak', 'desc' => 'Segmentasi kontak'],
                    ['icon' => 'fa-bullhorn', 'label' => 'Kampanye', 'desc' => 'Kirim pesan massal'],
                    ['icon' => 'fa-clock', 'label' => 'Jadwal Berulang', 'desc' => 'Auto-send berkala'],
                    ['icon' => 'fa-robot', 'label' => 'Auto-Reply', 'desc' => 'Balas otomatis keyword'],
                    ['icon' => 'fa-file-lines', 'label' => 'Template Pesan', 'desc' => 'Simpan template'],
                    ['icon' => 'fa-bolt', 'label' => 'Webhook', 'desc' => 'Forward pesan ke URL'],
                    ['icon' => 'fa-brain', 'label' => 'AI Keys', 'desc' => 'Integrasi AI/LLM'],
                ]
            ],
            [
                'title' => 'Sistem',
                'icon' => 'fa-cog',
                'items' => [
                    ['icon' => 'fa-server', 'label' => 'Server', 'desc' => 'Konfigurasi server (admin)'],
                    ['icon' => 'fa-users-cog', 'label' => 'User', 'desc' => 'Manajemen user (admin)'],
                    ['icon' => 'fa-ticket-alt', 'label' => 'Voucher', 'desc' => 'Kode voucher (admin)'],
                    ['icon' => 'fa-exchange-alt', 'label' => 'Transaksi', 'desc' => 'Riwayat pembayaran (admin)'],
                    ['icon' => 'fa-link', 'label' => 'Shortener', 'desc' => 'URL pendek (admin)'],
                    ['icon' => 'fa-file-alt', 'label' => 'CMS Pages', 'desc' => 'Halaman konten (admin)'],
                    ['icon' => 'fa-blog', 'label' => 'Blog', 'desc' => 'Kelola artikel (admin)'],
                    ['icon' => 'fa-hand-holding-usd', 'label' => 'Payout Admin', 'desc' => 'Pencairan (admin)'],
                    ['icon' => 'fa-box', 'label' => 'Paket', 'desc' => 'Paket langganan'],
                    ['icon' => 'fa-id-card', 'label' => 'Langganan', 'desc' => 'Status langganan'],
                    ['icon' => 'fa-key', 'label' => 'API Token', 'desc' => 'Token integrasi'],
                    ['icon' => 'fa-wallet', 'label' => 'Payout', 'desc' => 'Pencairan saldo'],
                    ['icon' => 'fa-history', 'label' => 'Log', 'desc' => 'Aktivitas log'],
                ]
            ],
        ];
    }

    protected function tutorialPhases(): array
    {
        return [
            [
                'phase' => 'Fase 1 — Setup Awal',
                'icon' => 'fa-rocket',
                'steps' => [
                    'Daftar akun di halaman register',
                    'Login dengan akun yang sudah dibuat',
                    'Masuk ke halaman Paket untuk memilih paket',
                    'Setup server WhatsApp di menu Server (admin)',
                ]
            ],
            [
                'phase' => 'Fase 2 — Koneksi WhatsApp',
                'icon' => 'fa-plug',
                'steps' => [
                    'Buka menu Sesi / Agen → Tambah Sesi',
                    'Pilih server dan masukkan nama sesi',
                    'Scan QR code dengan WhatsApp di HP Anda',
                    'Tunggu status berubah menjadi Connected',
                ]
            ],
            [
                'phase' => 'Fase 3 — Data Kontak',
                'icon' => 'fa-address-book',
                'steps' => [
                    'Buka menu Kontak → Tambah Kontak manual',
                    'Atau gunakan Import CSV untuk upload massal',
                    'Buat Grup Kontak untuk segmentasi',
                    'Assign kontak ke grup yang sesuai',
                ]
            ],
            [
                'phase' => 'Fase 4 — Auto-Reply',
                'icon' => 'fa-robot',
                'steps' => [
                    'Buka menu Auto-Reply → Tambah',
                    'Masukkan keyword pemicu (contoh: "harga")',
                    'Pilih tipe pencocokan: exact / contains',
                    'Tulis pesan balasan dengan spintax {Halo|Hai}',
                    'Aktifkan auto-reply',
                ]
            ],
            [
                'phase' => 'Fase 5 — Kampanye',
                'icon' => 'fa-bullhorn',
                'steps' => [
                    'Buka menu Kampanye → Tambah Kampanye',
                    'Isi nama, pesan, dan pilih sesi',
                    'Pilih kontak atau grup target',
                    'Atur delay antar pesan (anti-ban)',
                    'Jadwalkan atau kirim langsung',
                ]
            ],
            [
                'phase' => 'Fase 6 — Chat Harian',
                'icon' => 'fa-comments',
                'steps' => [
                    'Buka menu Chat untuk lihat inbox',
                    'Klik kontak untuk lihat percakapan',
                    'Balas pesan langsung dari dashboard',
                    'Pantau status: sent, delivered, read',
                ]
            ],
            [
                'phase' => 'Fase 7 — Integrasi',
                'icon' => 'fa-bolt',
                'steps' => [
                    'Setup Webhook untuk forward pesan',
                    'Generate API Token untuk integrasi eksternal',
                    'Konfigurasi AI Keys untuk auto-reply cerdas',
                    'Test integrasi dengan tools eksternal',
                ]
            ],
        ];
    }

    protected function features(): array
    {
        return [
            [
                'group' => 'Chat & Pesan',
                'icon' => 'fa-comments',
                'items' => [
                    ['title' => 'Chat Omni-Channel', 'desc' => 'Inbox real-time semua percakapan WhatsApp dalam satu layar. Lihat, balas, dan pantau status pesan (sent, delivered, read).'],
                    ['title' => 'Multi-Agen', 'desc' => 'Hubungkan banyak nomor WhatsApp sekaligus. Setiap sesi bisa punya nama dan agen berbeda.'],
                    ['title' => 'Template Pesan', 'desc' => 'Simpan template pesan untuk balasan cepat. Gunakan variabel dinamis untuk personalisasi.'],
                ]
            ],
            [
                'group' => 'Otomatisasi',
                'icon' => 'fa-robot',
                'items' => [
                    ['title' => 'Auto-Reply Keyword', 'desc' => 'Balas otomatis berdasarkan keyword dengan spintax {Halo|Hai}. Dukungan exact match dan contains.'],
                    ['title' => 'Jadwal Berulang', 'desc' => 'Kirim pesan welcome, daily, weekly, atau monthly otomatis. Set and forget.'],
                    ['title' => 'Campaign Bulk', 'desc' => 'Kirim pesan massal ke ribuan kontak dengan delay anti-ban. Bisa dijadwalkan atau kirim langsung.'],
                ]
            ],
            [
                'group' => 'Kontak',
                'icon' => 'fa-address-book',
                'items' => [
                    ['title' => 'Database Kontak', 'desc' => 'Kelola semua kontak WhatsApp. Tambah manual atau import CSV massal.'],
                    ['title' => 'Grup Kontak', 'desc' => 'Segmentasi kontak ke grup untuk targeting campaign yang lebih presisi.'],
                    ['title' => 'Import CSV', 'desc' => 'Upload file CSV sekali klik. Auto-detect kolom nama dan nomor.'],
                ]
            ],
            [
                'group' => 'Integrasi',
                'icon' => 'fa-plug',
                'items' => [
                    ['title' => 'Webhook', 'desc' => 'Forward pesan masuk ke URL eksternal. Konfigurasi header dan method HTTP.'],
                    ['title' => 'API Token', 'desc' => 'Generate token untuk integrasi dengan aplikasi lain via REST API.'],
                    ['title' => 'AI / LLM Keys', 'desc' => 'Tambahkan API key LLM untuk auto-reply cerdas berbasis AI. Dukung OpenAI, DeepSeek, Groq, dll.'],
                ]
            ],
            [
                'group' => 'Manajemen',
                'icon' => 'fa-cog',
                'items' => [
                    ['title' => 'Dashboard Analitik', 'desc' => 'Pantau aktivitas pesan dengan chart interaktif. Lihat tren harian, mingguan, bulanan.'],
                    ['title' => 'Paket Langganan', 'desc' => 'Pilih paket sesuai kebutuhan: Free, Growth, atau Whitelabel. Upgrade kapan saja.'],
                    ['title' => 'Payout & Saldo', 'desc' => 'Pantau saldo dan ajukan pencairan. Admin bisa approve/reject payout.'],
                ]
            ],
        ];
    }
}
