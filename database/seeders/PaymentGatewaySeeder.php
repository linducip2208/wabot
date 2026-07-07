<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['code' => 'bca',       'name' => 'Bank BCA',         'account_holder' => 'PT WABot Indonesia', 'logo_color' => '#0066ae', 'sort_order' => 1],
            ['code' => 'mandiri',   'name' => 'Bank Mandiri',     'account_holder' => 'PT WABot Indonesia', 'logo_color' => '#004b8d', 'sort_order' => 2],
            ['code' => 'bni',       'name' => 'Bank BNI',         'account_holder' => 'PT WABot Indonesia', 'logo_color' => '#f15a24', 'sort_order' => 3],
            ['code' => 'bri',       'name' => 'Bank BRI',         'account_holder' => 'PT WABot Indonesia', 'logo_color' => '#005098', 'sort_order' => 4],
            ['code' => 'midtrans',  'name' => 'Midtrans',         'account_holder' => null, 'logo_color' => '#1abc9c', 'sort_order' => 5],
            ['code' => 'xendit',    'name' => 'Xendit',           'account_holder' => null, 'logo_color' => '#635bff', 'sort_order' => 6],
            ['code' => 'tripay',    'name' => 'Tripay',           'account_holder' => null, 'logo_color' => '#0d6efd', 'sort_order' => 7],
            ['code' => 'doku',      'name' => 'Doku',             'account_holder' => null, 'logo_color' => '#ed2a7b', 'sort_order' => 8],
            ['code' => 'ipaymu',    'name' => 'iPaymu',           'account_holder' => null, 'logo_color' => '#e62b34', 'sort_order' => 9],
            ['code' => 'qris',      'name' => 'QRIS',             'account_holder' => 'PT WABot Indonesia', 'logo_color' => '#dc3545', 'sort_order' => 10],
            ['code' => 'gopay',     'name' => 'GoPay',            'account_holder' => '081234567890', 'logo_color' => '#00aa13', 'sort_order' => 11],
            ['code' => 'ovo',       'name' => 'OVO',              'account_holder' => '081234567890', 'logo_color' => '#4c2a85', 'sort_order' => 12],
            ['code' => 'dana',      'name' => 'DANA',             'account_holder' => '081234567890', 'logo_color' => '#108ee9', 'sort_order' => 13],
        ];

        foreach ($gateways as $g) {
            $instructions = match ($g['code']) {
                'bca' => "Transfer ke rekening BCA:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka BCA Mobile / KlikBCA\n2. Pilih Transfer → Antar Rekening BCA\n3. Masukkan nomor rekening di atas\n4. Masukkan nominal sesuai invoice\n5. Konfirmasi & kirim bukti transfer",
                'mandiri' => "Transfer ke rekening Mandiri:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka Livin' by Mandiri\n2. Pilih Transfer → Sesama Mandiri\n3. Masukkan nomor rekening di atas\n4. Masukkan nominal sesuai invoice\n5. Konfirmasi & upload bukti transfer",
                'bni' => "Transfer ke rekening BNI:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka BNI Mobile Banking\n2. Pilih Transfer → Sesama BNI\n3. Masukkan nomor rekening\n4. Masukkan nominal sesuai invoice\n5. Konfirmasi & kirim bukti",
                'bri' => "Transfer ke rekening BRI:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka BRImo\n2. Pilih Transfer → Sesama BRI\n3. Masukkan nomor rekening\n4. Masukkan nominal sesuai invoice\n5. Konfirmasi & upload bukti",
                'qris' => "Scan QRIS untuk pembayaran:\n\nScan kode QR di bawah menggunakan:\n• GoPay / Gojek\n• OVO\n• DANA\n• LinkAja\n• Mobile Banking\n\nPembayaran otomatis terverifikasi.",
                'gopay' => "Transfer GoPay:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka aplikasi Gojek\n2. Pilih GoPay → Transfer\n3. Masukkan nomor di atas\n4. Masukkan nominal\n5. Kirim & upload bukti transfer",
                'ovo' => "Transfer OVO:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka aplikasi OVO\n2. Pilih Transfer\n3. Masukkan nomor OVO di atas\n4. Masukkan nominal\n5. Kirim & upload bukti transfer",
                'dana' => "Transfer DANA:\n\nNomor: {no_rek}\nNama: {nama}\n\n1. Buka aplikasi DANA\n2. Pilih Kirim Uang\n3. Masukkan nomor DANA di atas\n4. Masukkan nominal\n5. Kirim & upload bukti transfer",
                default => "Pembayaran via {$g['name']}\n\nSilakan ikuti instruksi dari payment gateway.\nUpload bukti pembayaran setelah selesai.",
            };
            $instructions = str_replace(['{no_rek}', '{nama}'], [$g['account_number'] ?? '-', $g['account_holder'] ?? 'PT WABot Indonesia'], $instructions);

            PaymentGateway::updateOrCreate(
                ['code' => $g['code']],
                array_merge($g, ['instructions' => $instructions, 'is_active' => true]),
            );
        }
    }
}
