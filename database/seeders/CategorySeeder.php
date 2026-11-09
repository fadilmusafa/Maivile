<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Rapor', 'Ijazah', 'Sertifikat', 'Surat Keterangan', 'Dokumen Lainnya'] as $name) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                ['type' => 'document', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
