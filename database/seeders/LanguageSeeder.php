<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'name' => 'Bahasa Indonesia',
                'native_name' => 'Bahasa Indonesia',
                'iso' => 'id',
                'rtl' => false,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'English',
                'native_name' => 'English',
                'iso' => 'en',
                'rtl' => false,
                'is_active' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($languages as $data) {
            Language::updateOrCreate(['iso' => $data['iso']], $data);
        }
    }
}
