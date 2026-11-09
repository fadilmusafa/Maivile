<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['pinjam_rapor', 'Peminjaman Rapor', 'Peminjaman rapor fisik oleh siswa.'],
            ['legalisir', 'Legalisir Dokumen', 'Legalisir ijazah, rapor, atau dokumen akademik lain.'],
            ['pengambilan_ijazah', 'Pengambilan Ijazah', 'Pengambilan ijazah asli sesuai jadwal.'],
        ];
        foreach ($rows as [$code, $name, $desc]) {
            DB::table('service_types')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'description' => $desc, 'is_active' => true,
                 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
