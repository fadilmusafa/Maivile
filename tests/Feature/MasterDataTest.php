<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_student_cannot_access_master_data(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.service-types.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.categories.store'), ['name' => 'X'])->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_category(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Rapor', 'description' => 'Rapor semester'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Rapor')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.categories.update', $category), ['name' => 'Rapor Semester'])
            ->assertRedirect(route('admin.categories.index'));
        $this->assertSame('Rapor Semester', $category->fresh()->name);

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($category);
    }

    public function test_category_name_must_be_unique(): void
    {
        Category::create(['name' => 'Ijazah', 'type' => 'document']);

        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Ijazah'])
            ->assertSessionHasErrors('name');
    }

    public function test_category_in_use_cannot_be_deleted_even_by_soft_deleted_documents(): void
    {
        $category = Category::create(['name' => 'Ijazah', 'type' => 'document']);
        $doc = Document::create(['category_id' => $category->id, 'name' => 'Ijazah A', 'document_kind' => 'physical', 'availability' => 'tersedia']);
        $doc->delete(); // soft delete: baris masih ada dan menahan foreign key

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_service_type_can_be_updated_but_code_is_immutable(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir', 'code' => 'legalisir', 'is_active' => true]);

        $this->actingAs($this->admin)->put(route('admin.service-types.update', $service), [
            'name' => 'Legalisir Dokumen', 'description' => 'Baru', 'is_active' => 0, 'code' => 'hacked',
        ])->assertRedirect(route('admin.service-types.index'));

        $service->refresh();
        $this->assertSame('Legalisir Dokumen', $service->name);
        $this->assertFalse($service->is_active);
        $this->assertSame('legalisir', $service->code);
    }

    public function test_service_types_cannot_be_created_or_deleted_via_http(): void
    {
        $service = ServiceType::create(['name' => 'Legalisir', 'code' => 'legalisir']);

        $this->actingAs($this->admin)->delete('/admin/service-types/'.$service->id)->assertStatus(405);
        $this->actingAs($this->admin)->post('/admin/service-types', ['name' => 'Baru'])->assertStatus(405);
    }
}
