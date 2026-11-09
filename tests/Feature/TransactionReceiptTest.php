<?php

namespace Tests\Feature;

use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $owner;
    private User $other;
    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $service = ServiceType::create(['code' => 'legalisir', 'name' => 'Legalisir Dokumen', 'is_active' => true]);
        $this->admin = User::factory()->admin()->create();
        $this->owner = User::factory()->create(['name' => 'Alya Pemilik']);
        $this->other = User::factory()->create(['name' => 'Bima Lain']);

        $this->transaction = Transaction::unguarded(fn () => Transaction::create([
            'transaction_code' => 'MVL-T9-0001',
            'user_id' => $this->owner->id,
            'service_type_id' => $service->id,
            'request_date' => '2026-10-01',
            'approval_status' => 'pending',
            'process_status' => 'menunggu_verifikasi',
        ]));
        TransactionDetail::create(['transaction_id' => $this->transaction->id, 'legalization_doc_type' => 'Ijazah', 'copies' => 3]);
    }

    public function test_owner_can_download_receipt_pdf(): void
    {
        $response = $this->actingAs($this->owner)->get(route('transactions.receipt', $this->transaction));

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('bukti-mvl-t9-0001.pdf', (string) $response->headers->get('content-disposition'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'transaction_receipt_downloaded', 'user_id' => $this->owner->id]);
    }

    public function test_admin_can_download_any_receipt(): void
    {
        $this->actingAs($this->admin)->get(route('transactions.receipt', $this->transaction))->assertOk();
    }

    public function test_other_student_is_forbidden(): void
    {
        $this->actingAs($this->other)->get(route('transactions.receipt', $this->transaction))->assertForbidden();
        $this->assertDatabaseMissing('activity_logs', ['action' => 'transaction_receipt_downloaded']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transactions.receipt', $this->transaction))->assertRedirect(route('login'));
    }

    public function test_unknown_transaction_returns_404(): void
    {
        $this->actingAs($this->admin)->get('/transactions/999999/receipt')->assertNotFound();
    }

    public function test_receipt_button_is_shown_on_detail_pages(): void
    {
        $this->actingAs($this->owner)->get(route('student.transactions.show', $this->transaction))
            ->assertOk()->assertSee('Cetak bukti (PDF)')->assertSee(route('transactions.receipt', $this->transaction), false);

        $this->actingAs($this->admin)->get(route('admin.transactions.show', $this->transaction))
            ->assertOk()->assertSee('Cetak bukti (PDF)');
    }
}
