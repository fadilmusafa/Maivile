<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = DB::table('users')->where('email', 'admin@maivile.test')->value('id');
        $svc = fn ($c) => DB::table('service_types')->where('code', $c)->value('id');
        $student = fn ($nis) => DB::table('users')->where('nis', $nis)->first();
        $doc = fn ($number) => DB::table('documents')->where('document_number', $number)->value('id');

        $rows = [
            ['MVL-DEMO-0001', '2024001', 'pinjam_rapor', 'RPT-2024001-5', 'pending', 'menunggu_verifikasi', 5, 10, null],
            ['MVL-DEMO-0002', '2024002', 'legalisir', null, 'approved', 'sedang_diproses', null, null, 'Berkas lengkap, sedang diproses.'],
            ['MVL-DEMO-0003', '2024003', 'pengambilan_ijazah', 'IJZ-2024003', 'approved', 'jadwal_dikonfirmasi', 7, null, 'Bawa KTP/kartu pelajar asli.'],
            ['MVL-DEMO-0004', '2024001', 'legalisir', null, 'rejected', 'ditolak', null, null, 'Lampiran tidak terbaca, silakan ajukan ulang.'],
        ];

        foreach ($rows as [$code, $nis, $service, $docNo, $approval, $process, $plan, $ret, $note]) {
            if (DB::table('transactions')->where('transaction_code', $code)->exists()) {
                continue;
            }
            $u = $student($nis);
            $id = DB::table('transactions')->insertGetId([
                'transaction_code' => $code,
                'user_id' => $u->id,
                'service_type_id' => $svc($service),
                'document_id' => $docNo ? $doc($docNo) : null,
                'request_date' => now()->subDays(3)->toDateString(),
                'planned_date' => $plan ? now()->addDays($plan)->toDateString() : null,
                'planned_return_date' => $ret ? now()->addDays($ret)->toDateString() : null,
                'description' => 'Data demo untuk pengujian.',
                'approval_status' => $approval,
                'process_status' => $process,
                'admin_note' => $note,
                'processed_by' => $approval === 'pending' ? null : $admin,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('transaction_details')->insert([
                'transaction_id' => $id,
                'legalization_doc_type' => $service === 'legalisir' ? 'Ijazah' : null,
                'copies' => $service === 'legalisir' ? 3 : null,
                'contact_phone' => $u->phone,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('transaction_status_histories')->insert([
                'transaction_id' => $id, 'old_status' => null,
                'new_status' => 'menunggu_verifikasi', 'note' => 'Pengajuan dikirim.',
                'changed_by' => $u->id, 'changed_at' => now()->subDays(3),
            ]);
            if ($process !== 'menunggu_verifikasi') {
                DB::table('transaction_status_histories')->insert([
                    'transaction_id' => $id, 'old_status' => 'menunggu_verifikasi',
                    'new_status' => $process, 'note' => $note,
                    'changed_by' => $admin, 'changed_at' => now()->subDays(2),
                ]);
            }
        }
    }
}
