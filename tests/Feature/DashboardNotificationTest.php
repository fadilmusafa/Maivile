<?php

namespace Tests\Feature;

use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionStatusHistory;
use App\Models\User;
use App\Notifications\TransactionStatusChanged;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private User $other;
    private ServiceType $loan;
    private ServiceType $legal;
    private ServiceType $diploma;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loan = ServiceType::create(['code' => 'pinjam_rapor', 'name' => 'Peminjaman Rapor', 'is_active' => true]);
        $this->legal = ServiceType::create(['code' => 'legalisir', 'name' => 'Legalisir Dokumen', 'is_active' => true]);
        $this->diploma = ServiceType::create(['code' => 'pengambilan_ijazah', 'name' => 'Pengambilan Ijazah', 'is_active' => true]);

        $this->admin = User::factory()->admin()->create();
        $this->student = User::factory()->create(['name' => 'Alya Siswa']);
        $this->other = User::factory()->create(['name' => 'Bima Lain']);
    }

    private function makeTransaction(User $owner, ServiceType $service, string $approval, string $process, ?string $note = null): Transaction
    {
        $this->seq++;

        return Transaction::unguarded(fn () => Transaction::create([
            'transaction_code' => 'MVL-T6-'.str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'user_id' => $owner->id,
            'service_type_id' => $service->id,
            'request_date' => now()->toDateString(),
            'approval_status' => $approval,
            'process_status' => $process,
            'admin_note' => $note,
        ]));
    }

    private function history(Transaction $t, ?string $old, string $new, ?string $note = null): TransactionStatusHistory
    {
        return TransactionStatusHistory::create([
            'transaction_id' => $t->id,
            'old_status' => $old,
            'new_status' => $new,
            'note' => $note,
            'changed_by' => $this->admin->id,
            'changed_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------
    // Dashboard Admin
    // ------------------------------------------------------------------

    public function test_admin_dashboard_shows_service_summary_and_needs_action(): void
    {
        $this->makeTransaction($this->student, $this->loan, 'pending', 'menunggu_verifikasi');
        $this->makeTransaction($this->student, $this->legal, 'approved', 'sedang_diproses');
        $this->makeTransaction($this->other, $this->legal, 'approved', 'selesai');
        $this->makeTransaction($this->other, $this->diploma, 'rejected', 'ditolak');

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Ringkasan per Layanan')
            ->assertSee('Peminjaman Rapor')
            ->assertSee('Legalisir Dokumen')
            ->assertSee('Pengambilan Ijazah')
            ->assertSee('Perlu Tindakan Petugas')
            ->assertSee('MVL-T6-0001')   // menunggu verifikasi
            ->assertSee('MVL-T6-0002');  // sedang diproses

        $summary = $response->viewData('serviceSummary');
        $legal = $summary->first(fn ($r) => $r['service']->id === $this->legal->id);

        $this->assertSame(2, $legal['total']);
        $this->assertSame(1, $legal['active']);
        $this->assertSame(1, $legal['done']);
        // Hanya menunggu_verifikasi dan sedang_diproses yang perlu tindakan (selesai dan ditolak tidak).
        $this->assertSame(2, $response->viewData('needsActionCount'));
        $this->assertCount(2, $response->viewData('needsAction'));
    }

    public function test_needs_action_count_matches_admin_queue_count(): void
    {
        $this->makeTransaction($this->student, $this->loan, 'pending', 'menunggu_verifikasi');
        $this->makeTransaction($this->student, $this->legal, 'approved', 'siap_diambil');
        $this->makeTransaction($this->other, $this->diploma, 'pending', 'perlu_perbaikan');
        $this->makeTransaction($this->other, $this->legal, 'approved', 'selesai');

        $queueCount = $this->actingAs($this->admin)
            ->get(route('admin.transactions.index'))
            ->viewData('queueCount');

        $this->assertSame($queueCount, app(DashboardService::class)->needsActionCount());
        $this->assertSame(2, $queueCount);
    }

    public function test_admin_dashboard_monthly_chart_counts_current_month(): void
    {
        $this->makeTransaction($this->student, $this->loan, 'pending', 'menunggu_verifikasi');
        $this->makeTransaction($this->student, $this->legal, 'pending', 'menunggu_verifikasi');

        $monthly = $this->actingAs($this->admin)->get(route('admin.dashboard'))->viewData('monthly');

        $this->assertCount(6, $monthly);
        $this->assertSame(2, end($monthly)['count']);
        $this->assertSame(100, end($monthly)['pct']);
        $this->assertSame(0, $monthly[0]['count']);
    }

    public function test_admin_dashboard_works_without_any_transaction(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada pengajuan yang menunggu tindakan.');
    }

    // ------------------------------------------------------------------
    // Dashboard Siswa
    // ------------------------------------------------------------------

    public function test_student_dashboard_has_quick_actions_for_three_services(): void
    {
        $this->actingAs($this->student)->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee(route('student.loan.create'), false)
            ->assertSee(route('student.legalization.create'), false)
            ->assertSee(route('student.diploma.create'), false);
    }

    public function test_student_dashboard_only_shows_own_data(): void
    {
        $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui', 'Silakan ambil di TU.');
        $this->makeTransaction($this->other, $this->legal, 'rejected', 'ditolak', 'CATATAN-RAHASIA-SISWA-LAIN');

        $response = $this->actingAs($this->student)->get(route('student.dashboard'));

        $response->assertOk()
            ->assertSee('Silakan ambil di TU.')
            ->assertSee('MVL-T6-0001')
            ->assertDontSee('MVL-T6-0002')
            ->assertDontSee('CATATAN-RAHASIA-SISWA-LAIN');

        $summary = $response->viewData('serviceSummary');
        $this->assertSame(1, $summary->sum('total'));
        $this->assertSame(1, $summary->first(fn ($r) => $r['service']->id === $this->loan->id)['active']);
    }

    public function test_student_dashboard_shows_only_own_notifications(): void
    {
        $mine = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $theirs = $this->makeTransaction($this->other, $this->legal, 'approved', 'sedang_diproses');

        $this->student->notify(new TransactionStatusChanged($mine));
        $this->other->notify(new TransactionStatusChanged($theirs));

        $response = $this->actingAs($this->student)->get(route('student.dashboard'));

        $response->assertOk()
            ->assertSee('Pengajuan MVL-T6-0001')
            ->assertDontSee('Pengajuan MVL-T6-0002');
        $this->assertSame(1, $response->viewData('unreadCount'));
    }

    // ------------------------------------------------------------------
    // Notifikasi
    // ------------------------------------------------------------------

    public function test_notifications_page_requires_login(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    public function test_bell_shows_unread_count_in_header(): void
    {
        $t = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $this->student->notify(new TransactionStatusChanged($t));
        $this->student->notify(new TransactionStatusChanged($t));

        $this->actingAs($this->student)->get(route('student.transactions.index'))
            ->assertOk()
            ->assertSee('2 belum dibaca');
    }

    public function test_student_notification_list_shows_admin_note(): void
    {
        $t = $this->makeTransaction($this->student, $this->legal, 'approved', 'sedang_diproses');
        $this->student->notify(new TransactionStatusChanged($t, 'Berkas lengkap, sedang diproses.'));

        $this->actingAs($this->student)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Berkas lengkap, sedang diproses.');
    }

    public function test_opening_notification_marks_it_read_and_redirects_to_detail(): void
    {
        $t = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $this->student->notify(new TransactionStatusChanged($t));
        $notification = $this->student->notifications()->first();

        $this->actingAs($this->student)
            ->post(route('notifications.open', $notification->id))
            ->assertRedirect(route('student.transactions.show', $t));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $this->student->unreadNotifications()->count());
    }

    public function test_admin_notification_opens_admin_detail(): void
    {
        $t = $this->makeTransaction($this->student, $this->loan, 'pending', 'menunggu_verifikasi');
        $this->admin->notify(new \App\Notifications\TransactionSubmitted($t));
        $notification = $this->admin->notifications()->first();

        $this->actingAs($this->admin)
            ->post(route('notifications.open', $notification->id))
            ->assertRedirect(route('admin.transactions.show', $t));
    }

    public function test_user_cannot_open_someone_elses_notification(): void
    {
        $t = $this->makeTransaction($this->other, $this->loan, 'approved', 'disetujui');
        $this->other->notify(new TransactionStatusChanged($t));
        $notification = $this->other->notifications()->first();

        $this->actingAs($this->student)
            ->post(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_only_affects_current_user(): void
    {
        $mine = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $theirs = $this->makeTransaction($this->other, $this->legal, 'approved', 'sedang_diproses');
        $this->student->notify(new TransactionStatusChanged($mine));
        $this->other->notify(new TransactionStatusChanged($theirs));

        $this->actingAs($this->student)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $this->student->unreadNotifications()->count());
        $this->assertSame(1, $this->other->unreadNotifications()->count());
    }

    public function test_unread_filter_hides_read_notifications(): void
    {
        $a = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $b = $this->makeTransaction($this->student, $this->legal, 'approved', 'sedang_diproses');
        $this->student->notify(new TransactionStatusChanged($a));
        $this->student->notify(new TransactionStatusChanged($b));
        $this->student->notifications()->where('data', 'like', '%MVL-T6-0001%')->first()->markAsRead();

        $response = $this->actingAs($this->student)->get(route('notifications.index', ['filter' => 'unread']));

        $response->assertOk();
        $list = $response->viewData('notifications');
        $this->assertCount(1, $list);
        $this->assertSame('MVL-T6-0002', $list->first()->data['transaction_code']);
    }

    // ------------------------------------------------------------------
    // Riwayat status (Admin)
    // ------------------------------------------------------------------

    public function test_status_history_page_is_admin_only(): void
    {
        $this->get(route('admin.histories.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('admin.histories.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.histories.index'))->assertOk();
    }

    public function test_status_history_lists_and_filters(): void
    {
        $a = $this->makeTransaction($this->student, $this->loan, 'approved', 'disetujui');
        $b = $this->makeTransaction($this->other, $this->legal, 'rejected', 'ditolak');
        $this->history($a, 'menunggu_verifikasi', 'disetujui', 'Berkas sesuai');
        $this->history($b, 'menunggu_verifikasi', 'ditolak', 'Lampiran buram');

        $this->actingAs($this->admin)->get(route('admin.histories.index'))
            ->assertOk()
            ->assertSee('Berkas sesuai')
            ->assertSee('Lampiran buram');

        $this->actingAs($this->admin)->get(route('admin.histories.index', ['q' => 'Alya']))
            ->assertSee('Berkas sesuai')
            ->assertDontSee('Lampiran buram');

        $this->actingAs($this->admin)->get(route('admin.histories.index', ['status' => 'ditolak']))
            ->assertSee('Lampiran buram')
            ->assertDontSee('Berkas sesuai');

        $this->actingAs($this->admin)->get(route('admin.histories.index', ['service_type_id' => $this->loan->id]))
            ->assertSee('Berkas sesuai')
            ->assertDontSee('Lampiran buram');
    }

    public function test_sidebar_links_status_history_only_for_admin(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee(route('admin.histories.index'), false);

        $this->actingAs($this->student)->get(route('student.dashboard'))
            ->assertDontSee(route('admin.histories.index'), false);
    }
}
