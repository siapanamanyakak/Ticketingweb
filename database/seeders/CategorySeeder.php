<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hardware',  'description' => 'Masalah perangkat keras seperti komputer, printer, dll'],
            ['name' => 'Software',  'description' => 'Masalah aplikasi, instalasi, lisensi'],
            ['name' => 'Network',   'description' => 'Masalah jaringan, internet, VPN'],
            ['name' => 'Account',   'description' => 'Masalah akun, password, akses'],
            ['name' => 'Email',     'description' => 'Masalah email, konfigurasi mail client'],
            ['name' => 'General',     'description' => 'Masalah lainnya'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
