<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryKeyword;
use Illuminate\Database\Seeder;

class CategoryKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $keywords = [
            'Hardware'  => [
                'komputer', 'pc', 'laptop', 'printer', 'monitor', 'keyboard', 'mouse',
                'mati', 'blue screen', 'bsod', 'hang', 'freeze', 'rusak', 'layar',
                'proyektor', 'scanner', 'hardisk', 'ram', 'cpu', 'power supply',
                'kabel', 'ups', 'charger', 'baterai', 'fan', 'overheat',
            ],
            'Software'  => [
                'aplikasi', 'software', 'program', 'install', 'error', 'crash', 'update',
                'lisensi', 'aktivasi', 'windows', 'office', 'excel', 'word', 'powerpoint',
                'not responding', 'gagal', 'tidak bisa buka', 'virus', 'malware',
                'driver', 'corrupt', 'missing', 'uninstall',
            ],
            'Network'   => [
                'internet', 'jaringan', 'network', 'wifi', 'lan', 'vpn', 'koneksi',
                'lambat', 'putus', 'tidak bisa akses', 'website', 'timeout', 'ping',
                'ip address', 'dns', 'router', 'switch', 'access point', 'bandwidth',
            ],
            'Account'   => [
                'akun', 'account', 'password', 'login', 'akses', 'lupa password',
                'reset', 'username', 'blocked', 'terkunci', 'hak akses', 'permission',
                'user', 'aktivasi akun', 'nonaktif', 'ganti password',
            ],
            'Email'     => [
                'email', 'outlook', 'gmail', 'mail', 'kirim email', 'terima email',
                'attachment email', 'spam', 'bounce', 'inbox', 'tidak masuk',
                'signature', 'forward', 'cc', 'bcc',
            ],
        ];

        foreach ($keywords as $categoryName => $words) {
            $category = Category::where('name', $categoryName)->first();
            if ($category) {
                foreach ($words as $word) {
                    CategoryKeyword::create([
                        'category_id' => $category->id,
                        'keyword'     => $word,
                    ]);
                }
            }
        }
    }
}
