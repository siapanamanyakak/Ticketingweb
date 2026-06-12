<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameService
{
    public function generate(string $name): string
    {
        // Ambil kata pertama, lowercase
        $parts    = explode(' ', trim($name));
        $base     = strtolower($parts[0]);

        // Bersihkan karakter non-alphanumeric kecuali underscore
        $base = preg_replace('/[^a-z0-9_]/', '', $base);

        // Kalau base kosong setelah dibersihkan, pakai 'user'
        if (empty($base)) $base = 'user';

        // Cek apakah username sudah ada
        if (!User::where('username', $base)->exists()) {
            return $base;
        }

        // Coba tambah kata kedua
        if (isset($parts[1])) {
            $second   = strtolower($parts[1]);
            $second   = preg_replace('/[^a-z0-9_]/', '', $second);
            $combined = $base . '_' . $second;

            if (!User::where('username', $combined)->exists()) {
                return $combined;
            }
        }

        // Kalau masih duplikat, tambah angka
        $counter = 1;
        while (User::where('username', $base . '_' . $counter)->exists()) {
            $counter++;
        }

        return $base . '_' . $counter;
    }

    public function generateForExisting(): void
    {
        $users = User::whereNull('username')->get();
        foreach ($users as $user) {
            $user->update(['username' => $this->generate($user->name)]);
        }
    }
}
