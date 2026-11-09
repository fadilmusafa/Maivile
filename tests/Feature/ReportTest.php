<?php

namespace Tests\Feature;

use App\Exports\TransactionsExport;
use App\Models\ActivityLog;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private ServiceType $loan;
    private ServiceType $legal;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loan = ServiceType::create(['code' => 'pinjam_rapor', 'name' => 'Peminjaman Rapor', 'is_active' => true]);
        $this->legal = ServiceType::create(['code' => 'legalisir', 'name' => 'Legalisir Dokumen', 'is_active' => true]);

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->create(['name' => 'Alya Siswa']);
    }

    private function makeTransaction(ServiceType $service, string $approval, string $process, string $date, ?User $owner = null): Transaction
    {
        $this->seq++;

        return Transaction::unguarded(fn () => Transaction::create([
            'transaction_code' => 'MVL-T7-'.str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'user_id' => ($owner ?? $this->student)->id,
            'service_type_id' => $service->id,
            'request_date' => $date,
            'approval_status' => $approval,
            'process_status' => $process,
        ]));
    }

    private function noFilters(): array
    {
        return ['from' => null, 'to' => null, 'service_type_id' => null, 'approval' => null, 'process' => null];
    }

    // ------------------------------------------------------------------
    // Hak akses
    // ------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_any_report_route(): void
    {
        $this->actingAs($this->student);

        foreach (['index', 'print', 'pdf', 'excel'] as $name) {
            $this->get(route('admin.reports.'.$name))->assertForbidden();
        }
    }

    public function test_admin_can_open_report_page(): void
    {
        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');

        $this->actingAs($this->admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('MVL-T7-0001')
            ->assertSee('Unduh PDF')
            ->assertSee('Unduh Excel');
    }

    // ------------------------------------------------------------------
    // Filter dan ringkasan
    // ------------------------------------------------------------------

    public function test_report_filters_by_service_approval_and_period(): void
    {
        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');   // 1
        $this->makeTransaction($this->legal, 'approved', 'sedang_diproses', '2026-10-05');     // 2
        $this->makeTransaction($this->legal, 'rejected', 'ditolak', '2026-09-01');             // 3

        $this->actingAs($this->admin);

        $this->get(route('admin.reports.index', ['service_type_id' => $this->legal->id]))
            ->assertOk()->assertSee('MVL-T7-0002')->assertSee('MVL-T7-0003')->assertDontSee('MVL-T7-0001');

        $this->get(route('admin.reports.index', ['approval' => 'rejected']))
            ->assertOk()->assertSee('MVL-T7-0003')->assertDontSee('MVL-T7-0001')->assertDontSee('MVL-T7-0002');

        $this->get(route('admin.reports.index', ['from' => '2026-10-01', 'to' => '2026-10-31']))
            ->assertOk()->assertSee('MVL-T7-0001')->assertSee('MVL-T7-0002')->assertDontSee('MVL-T7-0003');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.reports.index', ['from' => 'bukan-tanggal']))->assertSessionHasErrors('from');
        $this->get(route('admin.reports.index', ['from' => '2026-10-10', 'to' => '2026-10-01']))->assertSessionHasErrors('to');
        $this->get(route('admin.reports.index', ['approval' => 'ngawur']))->assertSessionHasErrors('approval');
        $this->get(route('admin.reports.index', ['service_type_id' => 9999]))->assertSessionHasErrors('service_type_id');
    }

    public function test_summary_counts_are_correct(): void
    {
        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');
        $this->makeTransaction($this->loan, 'approved', 'selesai', '2026-10-02');
        $this->makeTransaction($this->legal, 'rejected', 'ditolak', '2026-10-03');

        $summary = app(ReportService::class)->summary($this->noFilters());

        $this->assertSame(3, $summary['total']);

        $byName = collect($summary['by_service'])->keyBy('name');
        $this->assertSame(2, $byName['Peminjaman Rapor']['total']);
        $this->assertSame(1, $byName['Peminjaman Rapor']['pending']);
        $this->assertSame(1, $byName['Peminjaman Rapor']['approved']);
        $this->assertSame(1, $byName['Peminjaman Rapor']['selesai']);
        $this->assertSame(1, $byName['Legalisir Dokumen']['rejected']);

        $this->assertSame(3, collect($summary['by_process'])->sum('total'));
    }

    // ------------------------------------------------------------------
    // Cetak, PDF, Excel
    // ------------------------------------------------------------------

    public function test_print_page_lists_filtered_data_and_logs_activity(): void
    {
        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');
        $this->makeTransaction($this->legal, 'approved', 'sedang_diproses', '2026-10-05');

        $this->actingAs($this->admin)
            ->get(route('admin.reports.print', ['service_type_id' => $this->loan->id]))
            ->assertOk()
            ->assertSee('Laporan Pengajuan Layanan Dokumen')
            ->assertSee('MVL-T7-0001')
            ->assertDontSee('MVL-T7-0002')
            ->assertSee('Alya Siswa');

        $this->assertDatabaseHas('activity_logs', ['action' => 'report.print', 'user_id' => $this->admin->id]);
    }

    public function test_pdf_download_returns_pdf(): void
    {
        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertSame(1, ActivityLog::where('action', 'report.pdf')->count());
    }

    public function test_excel_download_uses_export_with_filtered_query(): void
    {
        Excel::fake();
        $this->travelTo('2026-10-22 10:00:00');

        $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01');
        $this->makeTransaction($this->legal, 'approved', 'sedang_diproses', '2026-10-05');

        $this->actingAs($this->admin)
            ->get(route('admin.reports.excel', ['service_type_id' => $this->legal->id]))
            ->assertOk();

        Excel::assertDownloaded('laporan-pengajuan-20261022.xlsx', function (TransactionsExport $export) {
            return $export->query()->get()->pluck('transaction_code')->all() === ['MVL-T7-0002'];
        });

        $this->assertSame(1, ActivityLog::where('action', 'report.excel')->count());
    }

    public function test_export_rows_match_headings_and_block_formula_injection(): void
    {
        $evil = User::factory()->create(['name' => '=HYPERLINK("http://x")']);
        $t = $this->makeTransaction($this->loan, 'pending', 'menunggu_verifikasi', '2026-10-01', $evil);

        $export = new TransactionsExport(Transaction::query());
        $row = $export->map($t->fresh(['user', 'serviceType', 'document', 'processor']));

        $this->assertCount(count($export->headings()), $row);
        $this->assertSame("'=HYPERLINK(\"http://x\")", $row[3]);
        $this->assertSame('MVL-T7-0001', $row[0]);
    }
}
