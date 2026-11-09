<?php

namespace Tests\Feature;

use App\Enums\ServiceCode;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCodeGenerator;
use App\Services\TransactionWorkflowService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCodeRaceTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private ServiceType $legal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legal = ServiceType::create(['code' => 'legalisir', 'name' => 'Legalisir Dokumen', 'is_active' => true]);
        $this->student = User::factory()->create();

        // Kode yang sudah terpakai (mensimulasikan pengajuan lain yang masuk lebih dulu).
        Transaction::unguarded(fn () => Transaction::create([
            'transaction_code' => 'MVL-RACE-0001',
            'user_id' => User::factory()->create()->id,
            'service_type_id' => $this->legal->id,
            'request_date' => '2026-10-01',
            'approval_status' => 'pending',
            'process_status' => 'menunggu_verifikasi',
        ]));
    }

    private function fakeGenerator(array $codes): TransactionCodeGenerator
    {
        $fake = new class($codes) extends TransactionCodeGenerator {
            public int $calls = 0;

            public function __construct(private array $codes)
            {
            }

            public function next(): string
            {
                $code = $this->codes[min($this->calls, count($this->codes) - 1)];
                $this->calls++;

                return $code;
            }
        };

        $this->app->instance(TransactionCodeGenerator::class, $fake);

        return $fake;
    }

    private function submit(): Transaction
    {
        return app(TransactionWorkflowService::class)->submit(
            $this->student,
            ServiceCode::Legalisir,
            ['legalization_doc_type' => 'Ijazah', 'copies' => 2],
        );
    }

    public function test_duplicate_code_is_retried_with_a_new_number(): void
    {
        $fake = $this->fakeGenerator(['MVL-RACE-0001', 'MVL-RACE-0002']);

        $t = $this->submit();

        $this->assertSame('MVL-RACE-0002', $t->transaction_code);
        $this->assertSame(2, $fake->calls);
        $this->assertSame(2, Transaction::count());
        $this->assertDatabaseCount('transaction_details', 1);
        $this->assertDatabaseCount('transaction_status_histories', 1);
    }

    public function test_gives_up_after_three_attempts_without_leaving_partial_data(): void
    {
        $fake = $this->fakeGenerator(['MVL-RACE-0001']);

        try {
            $this->submit();
            $this->fail('Seharusnya melempar UniqueConstraintViolationException.');
        } catch (UniqueConstraintViolationException) {
            $this->assertSame(3, $fake->calls);
        }

        $this->assertSame(1, Transaction::count());
        $this->assertDatabaseCount('transaction_details', 0);
        $this->assertDatabaseCount('transaction_status_histories', 0);
    }
}
