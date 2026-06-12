<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\Priority;
use App\Models\PriorityKeyword;
use Illuminate\Database\Seeder;

class FullKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategoryKeywords();
        $this->seedPriorityKeywords();
    }

    private function seedCategoryKeywords(): void
    {
        $data = [
            'Hardware' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'kabel',      'weight' => 1],
                ['keyword' => 'cable',      'weight' => 1],
                ['keyword' => 'layar',      'weight' => 1],
                ['keyword' => 'screen',     'weight' => 1],
                ['keyword' => 'baterai',    'weight' => 1],
                ['keyword' => 'battery',    'weight' => 1],
                ['keyword' => 'kertas',     'weight' => 1],
                ['keyword' => 'paper',      'weight' => 1],
                ['keyword' => 'tinta',      'weight' => 1],
                ['keyword' => 'ink',        'weight' => 1],
                ['keyword' => 'colokan',    'weight' => 1],
                ['keyword' => 'charger',    'weight' => 1],
                // Bobot 3 — Identitas Benda
                ['keyword' => 'komputer',   'weight' => 3],
                ['keyword' => 'computer',   'weight' => 3],
                ['keyword' => 'laptop',     'weight' => 3],
                ['keyword' => 'notebook',   'weight' => 3],
                ['keyword' => 'printer',    'weight' => 3],
                ['keyword' => 'mouse',      'weight' => 3],
                ['keyword' => 'keyboard',   'weight' => 3],
                ['keyword' => 'monitor',    'weight' => 3],
                ['keyword' => 'scanner',    'weight' => 3],
                ['keyword' => 'headset',    'weight' => 3],
                ['keyword' => 'webcam',     'weight' => 3],
                ['keyword' => 'ups',        'weight' => 3],
                // Bobot 5 — Alat Vital User
                ['keyword' => 'motherboard','weight' => 5],
                ['keyword' => 'hardisk',    'weight' => 5],
                ['keyword' => 'hard disk',  'weight' => 5],
                ['keyword' => 'hard drive', 'weight' => 5],
                ['keyword' => 'ram',        'weight' => 5],
                ['keyword' => 'memory',     'weight' => 5],
                ['keyword' => 'ssd',        'weight' => 5],
                ['keyword' => 'gpu',        'weight' => 5],
                ['keyword' => 'blue screen','weight' => 5],
                ['keyword' => 'bsod',       'weight' => 5],
                ['keyword' => 'overheat',   'weight' => 5],
                ['keyword' => 'hang',       'weight' => 5],
            ],

            'Software' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'aplikasi',   'weight' => 1],
                ['keyword' => 'program',    'weight' => 1],
                ['keyword' => 'sistem',     'weight' => 1],
                ['keyword' => 'file',       'weight' => 1],
                ['keyword' => 'dokumen',    'weight' => 1],
                ['keyword' => 'document',   'weight' => 1],
                ['keyword' => 'error',      'weight' => 1],
                ['keyword' => 'crash',      'weight' => 1],
                ['keyword' => 'loading',    'weight' => 1],
                // Bobot 3 — Aplikasi Standar
                ['keyword' => 'excel',      'weight' => 3],
                ['keyword' => 'word',       'weight' => 3],
                ['keyword' => 'office',     'weight' => 3],
                ['keyword' => 'microsoft',  'weight' => 3],
                ['keyword' => 'windows',    'weight' => 3],
                ['keyword' => 'browser',    'weight' => 3],
                ['keyword' => 'chrome',     'weight' => 3],
                ['keyword' => 'antivirus',  'weight' => 3],
                ['keyword' => 'virus',      'weight' => 3],
                ['keyword' => 'malware',    'weight' => 3],
                ['keyword' => 'outlook',    'weight' => 3],
                ['keyword' => 'teams',      'weight' => 3],
                ['keyword' => 'zoom',       'weight' => 3],
                ['keyword' => 'install',    'weight' => 3],
                ['keyword' => 'update',     'weight' => 3],
                // Bobot 5 — Sistem Operasional
                ['keyword' => 'aplikasi absen',     'weight' => 5],
                ['keyword' => 'software keuangan',  'weight' => 5],
                ['keyword' => 'sistem hrd',         'weight' => 5],
                ['keyword' => 'attendance system',  'weight' => 5],
                ['keyword' => 'lisensi',            'weight' => 5],
                ['keyword' => 'license',            'weight' => 5],
                ['keyword' => 'aktivasi',           'weight' => 5],
                ['keyword' => 'activation',         'weight' => 5],
                // Bobot 10 — Proprietary / Hak Veto
                ['keyword' => 'erp',            'weight' => 10],
                ['keyword' => 'sistem manifes', 'weight' => 10],
                ['keyword' => 'ship it',        'weight' => 10],
            ],

            'Network' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'sinyal',     'weight' => 1],
                ['keyword' => 'signal',     'weight' => 1],
                ['keyword' => 'koneksi',    'weight' => 1],
                ['keyword' => 'connection', 'weight' => 1],
                ['keyword' => 'jaringan',   'weight' => 1],
                ['keyword' => 'network',    'weight' => 1],
                ['keyword' => 'nyambung',   'weight' => 1],
                ['keyword' => 'lemot',      'weight' => 1],
                ['keyword' => 'slow',       'weight' => 1],
                ['keyword' => 'lambat',     'weight' => 1],
                // Bobot 3 — Identitas Standar
                ['keyword' => 'wifi',       'weight' => 3],
                ['keyword' => 'wireless',   'weight' => 3],
                ['keyword' => 'internet',   'weight' => 3],
                ['keyword' => 'kabel lan',  'weight' => 3],
                ['keyword' => 'lan cable',  'weight' => 3],
                ['keyword' => 'indihome',   'weight' => 3],
                ['keyword' => 'biznet',     'weight' => 3],
                ['keyword' => 'bandwidth',  'weight' => 3],
                ['keyword' => 'ping',       'weight' => 3],
                ['keyword' => 'timeout',    'weight' => 3],
                // Bobot 5 — Backbone Jaringan
                ['keyword' => 'router',         'weight' => 5],
                ['keyword' => 'mikrotik',       'weight' => 5],
                ['keyword' => 'switch',         'weight' => 5],
                ['keyword' => 'access point',   'weight' => 5],
                ['keyword' => 'vpn',            'weight' => 5],
                ['keyword' => 'firewall',       'weight' => 5],
                ['keyword' => 'dns',            'weight' => 5],
                ['keyword' => 'ip address',     'weight' => 5],
                // Bobot 10 — Koneksi Kritis
                ['keyword' => 'radio marine',   'weight' => 10],
                ['keyword' => 'vsat',           'weight' => 10],
                ['keyword' => 'satelit',        'weight' => 10],
                ['keyword' => 'satellite',      'weight' => 10],
            ],

            'Server' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'penyimpanan',    'weight' => 1],
                ['keyword' => 'storage',        'weight' => 1],
                ['keyword' => 'data',           'weight' => 1],
                ['keyword' => 'folder',         'weight' => 1],
                ['keyword' => 'backup',         'weight' => 1],
                // Bobot 3 — Layanan Web
                ['keyword' => 'hosting',        'weight' => 3],
                ['keyword' => 'domain',         'weight' => 3],
                ['keyword' => 'website',        'weight' => 3],
                ['keyword' => 'web server',     'weight' => 3],
                ['keyword' => 'ftp',            'weight' => 3],
                ['keyword' => 'cloud',          'weight' => 3],
                ['keyword' => 'shared drive',   'weight' => 3],
                ['keyword' => 'nas',            'weight' => 3],
                // Bobot 5 — Jantung Sistem
                ['keyword' => 'server',         'weight' => 5],
                ['keyword' => 'database',       'weight' => 5],
                ['keyword' => 'cpanel',         'weight' => 5],
                ['keyword' => 'vps',            'weight' => 5],
                ['keyword' => 'localhost',      'weight' => 5],
                ['keyword' => 'local server',   'weight' => 5],
                ['keyword' => 'active directory','weight' => 5],
                ['keyword' => 'file server',    'weight' => 5],
                // Bobot 10 — Absolute Override
                ['keyword' => 'server pusat',   'weight' => 10],
                ['keyword' => 'datacenter',     'weight' => 10],
                ['keyword' => 'data center',    'weight' => 10],
                ['keyword' => 'sql server',     'weight' => 10],
                ['keyword' => 'sql',            'weight' => 10],
                ['keyword' => 'ransomware',     'weight' => 10],
                ['keyword' => 'server down',    'weight' => 10],
                ['keyword' => 'server mati',    'weight' => 10],
            ],

            'Infrastructure' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'tiang',          'weight' => 1],
                ['keyword' => 'pole',           'weight' => 1],
                ['keyword' => 'kamera',         'weight' => 1],
                ['keyword' => 'camera',         'weight' => 1],
                ['keyword' => 'mesin',          'weight' => 1],
                ['keyword' => 'machine',        'weight' => 1],
                ['keyword' => 'gedung',         'weight' => 1],
                ['keyword' => 'building',       'weight' => 1],
                // Bobot 3 — Identitas Benda
                ['keyword' => 'cctv',           'weight' => 3],
                ['keyword' => 'mesin absen',    'weight' => 3],
                ['keyword' => 'fingerprint',    'weight' => 3],
                ['keyword' => 'pabx',           'weight' => 3],
                ['keyword' => 'telepon kantor', 'weight' => 3],
                ['keyword' => 'office phone',   'weight' => 3],
                ['keyword' => 'intercom',       'weight' => 3],
                ['keyword' => 'access control', 'weight' => 3],
                ['keyword' => 'id card reader', 'weight' => 3],
                // Bobot 5 — Backbone Fisik
                ['keyword' => 'kabel fiber',        'weight' => 5],
                ['keyword' => 'fiber optic',        'weight' => 5],
                ['keyword' => 'panel listrik it',   'weight' => 5],
                ['keyword' => 'server rack',        'weight' => 5],
                ['keyword' => 'rack server',        'weight' => 5],
                ['keyword' => 'ups tower',          'weight' => 5],
                ['keyword' => 'genset it',          'weight' => 5],
                // Bobot 10 — Infrastruktur Utama
                ['keyword' => 'kabel laut',         'weight' => 10],
                ['keyword' => 'submarine cable',    'weight' => 10],
                ['keyword' => 'kabel optik utama',  'weight' => 10],
                ['keyword' => 'main fiber',         'weight' => 10],
                ['keyword' => 'backbone',           'weight' => 10],
            ],

            'Account' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'akses',      'weight' => 1],
                ['keyword' => 'access',     'weight' => 1],
                ['keyword' => 'masuk',      'weight' => 1],
                ['keyword' => 'minta',      'weight' => 1],
                ['keyword' => 'request',    'weight' => 1],
                ['keyword' => 'gagal',      'weight' => 1],
                ['keyword' => 'failed',     'weight' => 1],
                ['keyword' => 'blocked',    'weight' => 1],
                ['keyword' => 'locked',     'weight' => 1],
                // Bobot 3 — Identitas Masalah
                ['keyword' => 'password',   'weight' => 3],
                ['keyword' => 'sandi',      'weight' => 3],
                ['keyword' => 'akun',       'weight' => 3],
                ['keyword' => 'account',    'weight' => 3],
                ['keyword' => 'login',      'weight' => 3],
                ['keyword' => 'username',   'weight' => 3],
                ['keyword' => 'user id',    'weight' => 3],
                ['keyword' => 'email',      'weight' => 3],
                ['keyword' => 'id',         'weight' => 3],
                // Bobot 5 — Aksi Spesifik
                ['keyword' => 'reset password',     'weight' => 5],
                ['keyword' => 'hak akses',          'weight' => 5],
                ['keyword' => 'permission',         'weight' => 5],
                ['keyword' => 'buka blokir',        'weight' => 5],
                ['keyword' => 'unblock',            'weight' => 5],
                ['keyword' => 'unlock account',     'weight' => 5],
                ['keyword' => 'two factor',         'weight' => 5],
                ['keyword' => '2fa',                'weight' => 5],
                // Bobot 10 — Hak Veto
                ['keyword' => 'admin privilege',    'weight' => 10],
                ['keyword' => 'akses superadmin',   'weight' => 10],
                ['keyword' => 'superadmin',         'weight' => 10],
                ['keyword' => 'domain admin',       'weight' => 10],
            ],

            'Email' => [
                // Bobot 1 — Ambigu/Umum
                ['keyword' => 'pesan',          'weight' => 1],
                ['keyword' => 'message',        'weight' => 1],
                ['keyword' => 'kirim',          'weight' => 1],
                ['keyword' => 'send',           'weight' => 1],
                ['keyword' => 'terima',         'weight' => 1],
                ['keyword' => 'receive',        'weight' => 1],
                ['keyword' => 'kotak masuk',    'weight' => 1],
                ['keyword' => 'inbox',          'weight' => 1],
                // Bobot 3 — Identitas Email
                ['keyword' => 'email',          'weight' => 3],
                ['keyword' => 'e-mail',         'weight' => 3],
                ['keyword' => 'outlook',        'weight' => 3],
                ['keyword' => 'gmail',          'weight' => 3],
                ['keyword' => 'mail',           'weight' => 3],
                ['keyword' => 'webmail',        'weight' => 3],
                ['keyword' => 'spam',           'weight' => 3],
                ['keyword' => 'attachment',     'weight' => 3],
                ['keyword' => 'lampiran',       'weight' => 3],
                ['keyword' => 'forward',        'weight' => 3],
                // Bobot 5 — Masalah Spesifik
                ['keyword' => 'email bounce',       'weight' => 5],
                ['keyword' => 'email tidak masuk',  'weight' => 5],
                ['keyword' => 'email tidak terkirim','weight' => 5],
                ['keyword' => 'blacklist',          'weight' => 5],
                ['keyword' => 'email server',       'weight' => 5],
                ['keyword' => 'smtp',               'weight' => 5],
                ['keyword' => 'imap',               'weight' => 5],
                ['keyword' => 'pop3',               'weight' => 5],
                // Bobot 10 — Hak Veto
                ['keyword' => 'mail server down',   'weight' => 10],
                ['keyword' => 'exchange server',    'weight' => 10],
                ['keyword' => 'email seluruh kantor','weight' => 10],
            ],
        ];

        foreach ($data as $categoryName => $keywords) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                // Buat kategori baru kalau belum ada
                $priorityMap = [
                    'Server'         => ['base' => 'high',   'max' => 'critical'],
                    'Infrastructure' => ['base' => 'medium', 'max' => 'critical'],
                ];

                $category = Category::create([
                    'name' => $categoryName,
                    'description'   => "Kategori {$categoryName}",
                    'base_priority' => $priorityMap[$categoryName]['base'] ?? 'low',
                    'max_priority'  => $priorityMap[$categoryName]['max'] ?? 'high',
                    'is_active'     => true,
                ]);
            }

            foreach ($keywords as $kw) {
                CategoryKeyword::firstOrCreate(
                    ['category_id' => $category->id, 'keyword' => $kw['keyword']],
                    ['weight' => $kw['weight']]
                );
            }
        }
    }

    private function seedPriorityKeywords(): void
    {
        $keywords = [
            'critical' => [
                // Bobot 10 — Entire business impacted
                ['keyword' => 'seluruh kantor',         'weight' => 10],
                ['keyword' => 'semua tidak bisa',       'weight' => 10],
                ['keyword' => 'sistem lumpuh',          'weight' => 10],
                ['keyword' => 'operasional terhenti',   'weight' => 10],
                ['keyword' => 'entire company',         'weight' => 10],
                ['keyword' => 'company wide',           'weight' => 10],
                ['keyword' => 'all users',              'weight' => 10],
                ['keyword' => 'production down',        'weight' => 10],
                // Bobot 5 — Strong indicator
                ['keyword' => 'darurat',        'weight' => 5],
                ['keyword' => 'emergency',      'weight' => 5],
                ['keyword' => 'urgent',         'weight' => 5],
                ['keyword' => 'segera',         'weight' => 5],
                ['keyword' => 'kritis',         'weight' => 5],
                ['keyword' => 'critical',       'weight' => 5],
                ['keyword' => 'tidak bisa kerja','weight' => 5],
                ['keyword' => 'cannot work',    'weight' => 5],
                // Bobot 3 — Moderate indicator
                ['keyword' => 'semua',          'weight' => 3],
                ['keyword' => 'seluruh',        'weight' => 3],
                ['keyword' => 'all',            'weight' => 3],
                ['keyword' => 'down',           'weight' => 3],
                ['keyword' => 'mati total',     'weight' => 3],
            ],

            'high' => [
                // Bobot 10 — Large group affected
                ['keyword' => 'satu departemen tidak bisa', 'weight' => 10],
                ['keyword' => 'seluruh tim',                'weight' => 10],
                ['keyword' => 'entire team',                'weight' => 10],
                ['keyword' => 'group affected',             'weight' => 10],
                ['keyword' => 'pekerjaan terhenti',         'weight' => 10],
                ['keyword' => 'work blocked',               'weight' => 10],
                // Bobot 5 — Strong indicator
                ['keyword' => 'banyak user',        'weight' => 5],
                ['keyword' => 'many users',         'weight' => 5],
                ['keyword' => 'beberapa orang',     'weight' => 5],
                ['keyword' => 'tim tidak bisa',     'weight' => 5],
                ['keyword' => 'team cannot',        'weight' => 5],
                ['keyword' => 'mendesak',           'weight' => 5],
                ['keyword' => 'penting',            'weight' => 5],
                ['keyword' => 'important',          'weight' => 5],
                // Bobot 3 — Moderate indicator
                ['keyword' => 'cepat',          'weight' => 3],
                ['keyword' => 'fast',           'weight' => 3],
                ['keyword' => 'segera tangani', 'weight' => 3],
                ['keyword' => 'hari ini',       'weight' => 3],
                ['keyword' => 'today',          'weight' => 3],
                ['keyword' => 'deadline',       'weight' => 3],
            ],

            'medium' => [
                // Bobot 5 — Single user, workaround available
                ['keyword' => 'saya sendiri',       'weight' => 5],
                ['keyword' => 'laptop saya',        'weight' => 5],
                ['keyword' => 'komputer saya',      'weight' => 5],
                ['keyword' => 'akun saya',          'weight' => 5],
                ['keyword' => 'my laptop',          'weight' => 5],
                ['keyword' => 'my computer',        'weight' => 5],
                ['keyword' => 'my account',         'weight' => 5],
                ['keyword' => 'bisa pakai alternatif','weight' => 5],
                ['keyword' => 'ada solusi lain',    'weight' => 5],
                // Bobot 3 — Moderate indicator
                ['keyword' => 'agak',           'weight' => 3],
                ['keyword' => 'lumayan',        'weight' => 3],
                ['keyword' => 'cukup penting',  'weight' => 3],
                ['keyword' => 'fairly',         'weight' => 3],
                ['keyword' => 'moderate',       'weight' => 3],
                ['keyword' => 'sesegera mungkin','weight' => 3],
                ['keyword' => 'as soon as possible','weight' => 3],
                ['keyword' => 'asap',           'weight' => 3],
            ],
        ];

        foreach ($keywords as $level => $words) {
            $priority = Priority::where('level', $level)->first();
            if (!$priority) continue;

            foreach ($words as $kw) {
                PriorityKeyword::firstOrCreate(
                    ['priority_id' => $priority->id, 'keyword' => $kw['keyword']],
                    ['weight' => $kw['weight']]
                );
            }
        }
    }
}
