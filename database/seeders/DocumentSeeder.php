<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn ($n) => DB::table('categories')->where('name', $n)->value('id');

        foreach (DB::table('users')->where('role', 'student')->get() as $u) {
            $docs = [
                ["Rapor Semester 5 - {$u->name}", 'Rapor', "RPT-{$u->nis}-5", '2025/2026'],
                ["Ijazah Asli - {$u->name}", 'Ijazah', "IJZ-{$u->nis}", '2025/2026'],
            ];
            foreach ($docs as [$name, $category, $number, $year]) {
                DB::table('documents')->updateOrInsert(
                    ['document_number' => $number],
                    ['category_id' => $cat($category), 'owner_user_id' => $u->id, 'name' => $name,
                     'academic_year' => $year, 'document_kind' => 'physical',
                     'availability' => 'tersedia', 'physical_location' => 'Lemari Arsip TU',
                     'description' => 'Dokumen fisik; belum ada file digital.',
                     'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
