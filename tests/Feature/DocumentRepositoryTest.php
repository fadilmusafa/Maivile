<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentAvailability;
use App\Enums\ProcessStatus;
use App\Models\Category;
use App\Models\Document;
use App\Models\ServiceType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
        $this->category = Category::create(['name' => 'Rapor', 'type' => 'document']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Rapor Semester 5',
            'category_id' => $this->category->id,
            'document_kind' => 'physical',
            'availability' => 'tersedia',
        ], $override);
    }

    private function digitalDocument(?User $owner, string $content = '%PDF-1.4 isi rahasia'): Document
    {
        Storage::disk('local')->put('documents/contoh.pdf', $content);

        return Document::create([
            'category_id' => $this->category->id, 'owner_user_id' => $owner?->id,
            'name' => 'Rapor Rahasia', 'document_kind' => 'digital', 'availability' => 'tersedia',
            'file_path' => 'documents/contoh.pdf', 'file_mime' => 'application/pdf',
        ]);
    }

    // ---------- upload ----------

    public function test_admin_uploads_digital_pdf_to_private_storage_with_random_name(): void
    {
        $owner = User::factory()->create();
        $file = UploadedFile::fake()->create('rapor-asli-siswa.pdf', 100, 'application/pdf');

        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload([
            'document_kind' => 'digital', 'owner_user_id' => $owner->id, 'file' => $file,
        ]))->assertSessionHasNoErrors();

        $doc = Document::firstOrFail();
        $this->assertTrue($doc->hasDigitalFile());
        $this->assertStringStartsWith('documents/', $doc->file_path);
        $this->assertStringNotContainsString('rapor-asli-siswa', $doc->file_path);
        $this->assertStringEndsWith('.pdf', $doc->file_path);
        Storage::disk('local')->assertExists($doc->file_path);
    }

    public function test_digital_document_requires_a_file(): void
    {
        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload(['document_kind' => 'digital']))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_dangerous_or_oversized_files_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload([
            'document_kind' => 'digital',
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        ]))->assertSessionHasErrors('file');

        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload([
            'document_kind' => 'digital',
            'file' => UploadedFile::fake()->create('terlalu-besar.pdf', 3000, 'application/pdf'),
        ]))->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_physical_document_stores_metadata_only_and_rejects_file(): void
    {
        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload([
            'name' => 'Ijazah Asli', 'physical_location' => 'Lemari A',
        ]))->assertSessionHasNoErrors();

        $doc = Document::firstOrFail();
        $this->assertFalse($doc->hasDigitalFile());
        $this->assertNull($doc->file_path);

        $this->actingAs($this->admin)->post(route('admin.documents.store'), $this->payload([
            'file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('file');
    }

    public function test_switching_to_physical_removes_the_stored_file(): void
    {
        $doc = $this->digitalDocument(null);

        $this->actingAs($this->admin)->put(route('admin.documents.update', $doc), $this->payload(['document_kind' => 'physical']))
            ->assertSessionHasNoErrors();

        $doc->refresh();
        $this->assertNull($doc->file_path);
        Storage::disk('local')->assertMissing('documents/contoh.pdf');
    }

    public function test_borrowed_availability_is_not_overwritten_by_edit_form(): void
    {
        $doc = Document::create([
            'category_id' => $this->category->id, 'name' => 'Rapor', 'document_kind' => 'physical',
            'availability' => DocumentAvailability::Dipinjam->value,
        ]);

        $this->actingAs($this->admin)->put(route('admin.documents.update', $doc), $this->payload(['availability' => 'tersedia']));

        $this->assertSame(DocumentAvailability::Dipinjam, $doc->fresh()->availability);
    }

    public function test_student_cannot_manage_documents(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.documents.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.documents.store'), $this->payload())->assertForbidden();
    }

    public function test_document_used_by_transaction_cannot_be_deleted(): void
    {
        $student = User::factory()->create();
        $doc = Document::create(['category_id' => $this->category->id, 'owner_user_id' => $student->id, 'name' => 'Rapor', 'document_kind' => 'physical', 'availability' => 'tersedia']);
        $service = ServiceType::create(['name' => 'Pinjam Rapor', 'code' => 'pinjam_rapor']);
        Transaction::forceCreate([
            'transaction_code' => 'MVL-D-0001', 'user_id' => $student->id, 'service_type_id' => $service->id,
            'document_id' => $doc->id, 'request_date' => now()->toDateString(),
            'approval_status' => ApprovalStatus::Pending, 'process_status' => ProcessStatus::MenungguVerifikasi,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.documents.destroy', $doc))->assertSessionHas('error');

        $this->assertNotSoftDeleted($doc);
    }

    public function test_unused_document_can_be_deleted(): void
    {
        $doc = Document::create(['category_id' => $this->category->id, 'name' => 'Rapor', 'document_kind' => 'physical', 'availability' => 'tersedia']);

        $this->actingAs($this->admin)->delete(route('admin.documents.destroy', $doc))->assertRedirect(route('admin.documents.index'));

        $this->assertSoftDeleted($doc);
    }

    // ---------- download & otorisasi ----------

    public function test_owner_can_download_own_file_as_attachment_with_safe_headers(): void
    {
        $owner = User::factory()->create();
        $doc = $this->digitalDocument($owner);

        $response = $this->actingAs($owner)->get(route('documents.download', $doc));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_inline_view_uses_inline_disposition(): void
    {
        $owner = User::factory()->create();
        $doc = $this->digitalDocument($owner);

        $response = $this->actingAs($owner)->get(route('documents.download', [$doc, 'inline' => 1]));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_other_student_cannot_download_someone_elses_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $doc = $this->digitalDocument($owner);

        $this->actingAs($intruder)->get(route('documents.download', $doc))->assertForbidden();
    }

    public function test_admin_can_download_any_file(): void
    {
        $doc = $this->digitalDocument(User::factory()->create());

        $this->actingAs($this->admin)->get(route('documents.download', $doc))->assertOk();
    }

    public function test_guest_is_redirected_to_login_when_downloading(): void
    {
        $doc = $this->digitalDocument(User::factory()->create());

        $this->get(route('documents.download', $doc))->assertRedirect(route('login'));
    }

    public function test_general_document_without_owner_is_admin_only(): void
    {
        $doc = $this->digitalDocument(null);

        $this->actingAs(User::factory()->create())->get(route('documents.download', $doc))->assertForbidden();
        $this->actingAs($this->admin)->get(route('documents.download', $doc))->assertOk();
    }

    public function test_physical_document_has_no_downloadable_file(): void
    {
        $owner = User::factory()->create();
        $doc = Document::create(['category_id' => $this->category->id, 'owner_user_id' => $owner->id, 'name' => 'Ijazah Asli', 'document_kind' => 'physical', 'availability' => 'tersedia']);

        $this->actingAs($owner)->get(route('documents.download', $doc))->assertForbidden();
        $this->actingAs($this->admin)->get(route('documents.download', $doc))->assertForbidden();
    }

    public function test_missing_file_on_disk_returns_404(): void
    {
        $owner = User::factory()->create();
        $doc = $this->digitalDocument($owner);
        Storage::disk('local')->delete('documents/contoh.pdf');

        $this->actingAs($owner)->get(route('documents.download', $doc))->assertNotFound();
    }

    // ---------- repositori siswa ----------

    public function test_student_repository_lists_only_own_documents(): void
    {
        $alya = User::factory()->create();
        $bima = User::factory()->create();
        Document::create(['category_id' => $this->category->id, 'owner_user_id' => $alya->id, 'name' => 'Rapor Milik Alya', 'document_kind' => 'physical', 'availability' => 'tersedia']);
        Document::create(['category_id' => $this->category->id, 'owner_user_id' => $bima->id, 'name' => 'Rapor Milik Bima', 'document_kind' => 'physical', 'availability' => 'tersedia']);

        $this->actingAs($alya)->get(route('student.documents.index'))
            ->assertOk()
            ->assertSee('Rapor Milik Alya')
            ->assertDontSee('Rapor Milik Bima');
    }

    public function test_admin_cannot_open_student_repository_page(): void
    {
        $this->actingAs($this->admin)->get(route('student.documents.index'))->assertForbidden();
    }
}
