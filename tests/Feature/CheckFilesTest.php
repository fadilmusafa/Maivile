<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\TransactionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $category = Category::create(['name' => 'Rapor', 'type' => 'document']);
        $owner = User::factory()->create();

        // Dokumen aktif dan dokumen yang sudah di-soft-delete: keduanya memegang berkas.
        foreach (['documents/aktif.pdf' => false, 'documents/terhapus.pdf' => true] as $path => $trashed) {
            Storage::disk('local')->put($path, 'x');
            $doc = Document::unguarded(fn () => Document::create([
                'category_id' => $category->id, 'owner_user_id' => $owner->id, 'name' => basename($path),
                'document_kind' => 'digital', 'availability' => 'tersedia', 'file_path' => $path,
            ]));
            if ($trashed) {
                $doc->delete();
            }
        }

        $service = ServiceType::create(['code' => 'legalisir', 'name' => 'Legalisir', 'is_active' => true]);
        $t = Transaction::unguarded(fn () => Transaction::create([
            'transaction_code' => 'MVL-F-0001', 'user_id' => $owner->id, 'service_type_id' => $service->id,
            'request_date' => '2026-10-01', 'approval_status' => 'pending', 'process_status' => 'menunggu_verifikasi',
        ]));
        Storage::disk('local')->put('transaction-files/lampiran.pdf', 'x');
        TransactionFile::unguarded(fn () => TransactionFile::create([
            'transaction_id' => $t->id, 'uploaded_by' => $owner->id, 'type' => 'requirement',
            'original_name' => 'a.pdf', 'file_path' => 'transaction-files/lampiran.pdf', 'mime_type' => 'application/pdf', 'file_size' => 1,
        ]));
    }

    public function test_registered_files_are_not_reported(): void
    {
        $this->artisan('maivile:files-check', ['--min-age' => 0])
            ->expectsOutputToContain('Tidak ada berkas yatim')
            ->assertSuccessful();
    }

    public function test_orphans_are_listed_but_not_deleted_by_default(): void
    {
        Storage::disk('local')->put('documents/yatim.pdf', 'x');
        Storage::disk('local')->put('transaction-files/yatim2.png', 'x');

        $this->artisan('maivile:files-check', ['--min-age' => 0])
            ->expectsOutputToContain('documents/yatim.pdf')
            ->expectsOutputToContain('transaction-files/yatim2.png')
            ->assertSuccessful();

        Storage::disk('local')->assertExists('documents/yatim.pdf');
        Storage::disk('local')->assertExists('transaction-files/yatim2.png');
    }

    public function test_fresh_files_are_skipped_by_default_min_age(): void
    {
        Storage::disk('local')->put('documents/baru-diunggah.pdf', 'x');

        $this->artisan('maivile:files-check')->expectsOutputToContain('Tidak ada berkas yatim')->assertSuccessful();

        Storage::disk('local')->assertExists('documents/baru-diunggah.pdf');
    }

    public function test_delete_removes_only_orphans_after_confirmation(): void
    {
        Storage::disk('local')->put('documents/yatim.pdf', 'x');

        $this->artisan('maivile:files-check', ['--delete' => true, '--min-age' => 0])
            ->expectsConfirmation('Hapus 1 berkas yatim ini secara permanen?', 'yes')
            ->expectsOutputToContain('1 berkas yatim dihapus')
            ->assertSuccessful();

        Storage::disk('local')->assertMissing('documents/yatim.pdf');
        Storage::disk('local')->assertExists('documents/aktif.pdf');
        Storage::disk('local')->assertExists('documents/terhapus.pdf');
        Storage::disk('local')->assertExists('transaction-files/lampiran.pdf');
    }

    public function test_declining_confirmation_deletes_nothing(): void
    {
        Storage::disk('local')->put('documents/yatim.pdf', 'x');

        $this->artisan('maivile:files-check', ['--delete' => true, '--min-age' => 0])
            ->expectsConfirmation('Hapus 1 berkas yatim ini secara permanen?', 'no')
            ->assertSuccessful();

        Storage::disk('local')->assertExists('documents/yatim.pdf');
    }

    public function test_force_deletes_without_asking(): void
    {
        Storage::disk('local')->put('documents/yatim.pdf', 'x');

        $this->artisan('maivile:files-check', ['--delete' => true, '--force' => true, '--min-age' => 0])->assertSuccessful();

        Storage::disk('local')->assertMissing('documents/yatim.pdf');
    }

    public function test_missing_files_are_reported_and_command_fails(): void
    {
        Storage::disk('local')->delete('documents/aktif.pdf');

        $this->artisan('maivile:files-check', ['--min-age' => 0])
            ->expectsOutputToContain('documents/aktif.pdf')
            ->assertFailed();
    }
}
