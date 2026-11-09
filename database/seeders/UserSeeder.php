<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya untuk pengujian lokal. Jangan dipakai di produksi.
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@maivile.test'],
            ['name' => 'Petugas Tata Usaha', 'password' => Hash::make('Admin#12345'),
             'role' => 'admin', 'nip' => '198001012005011001', 'phone' => '081200000001',
             'is_active' => true, 'must_change_password' => false,
             'created_at' => now(), 'updated_at' => now()]
        );

        $students = [
            ['2024001', 'Alya Putri Maharani', 'XII-IPA-1', 'IPA'],
            ['2024002', 'Bima Satria Nugroho', 'XII-IPS-2', 'IPS'],
            ['2024003', 'Citra Dewi Lestari', 'XII-IPA-2', 'IPA'],
        ];
        foreach ($students as [$nis, $name, $class, $major]) {
            DB::table('users')->updateOrInsert(
                ['nis' => $nis],
                ['name' => $name, 'email' => "siswa{$nis}@maivile.test",
                 'password' => Hash::make('Siswa#12345'), 'role' => 'student',
                 'class_name' => $class, 'major' => $major, 'phone' => '0812000' . $nis,
                 'is_active' => true, 'must_change_password' => false,
                 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
