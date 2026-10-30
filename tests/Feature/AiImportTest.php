<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        Outlet::create([
            'name' => 'Outlet Test',
            'require_open_shift' => false,
            'allow_negative_stock' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Owner AI',
            'email' => 'owner@ai.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $cat = Category::create(['name' => 'Kategori', 'sort_order' => 1, 'is_active' => true]);
        $this->menu = Menu::create(['category_id' => $cat->id, 'name' => 'Nasi Goreng', 'price' => 20000, 'is_active' => true]);
    }

    protected function createFakeJpg(string $filename): UploadedFile
    {
        // 1x1 transparent/black minimal valid JPEG
        $rawJpg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        return UploadedFile::fake()->createWithContent($filename, $rawJpg);
    }

    public function test_upload_screenshot_creates_import_record(): void
    {
        $file = $this->createFakeJpg('gofood-order.jpg');

        $response = $this->actingAs($this->owner)->post('/impor', [
            'screenshot' => $file,
            'channel' => 'gofood',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ai_imports', [
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_cannot_save_duplicate_platform_order_id(): void
    {
        // First, record a transaction with platform order ID GF-1234
        app(SalesService::class)->storeTransaction([
            'channel' => 'gofood',
            'payment_method' => 'other',
            'platform_order_id' => 'GF-1234',
            'items' => [['menu_id' => $this->menu->id, 'qty' => 1]],
        ], $this->owner->id);

        // Upload and attempt to save another import with identical platform order ID GF-1234
        $file = $this->createFakeJpg('gofood-order-2.jpg');
        $this->actingAs($this->owner)->post('/impor', ['screenshot' => $file]);

        $import = \App\Models\AiImport::latest()->first();

        $res = $this->actingAs($this->owner)->post("/impor/{$import->id}/simpan", [
            'channel' => 'gofood',
            'platform_order_id' => 'GF-1234',
            'items' => [
                ['menu_id' => $this->menu->id, 'qty' => 1, 'source_name' => 'Nasi Goreng'],
            ],
        ]);

        $res->assertSessionHas('error');
        $this->assertNotEquals('saved', $import->fresh()->status->value);
    }

    public function test_review_page_handles_missing_keys_without_error(): void
    {
        $import = \App\Models\AiImport::create([
            'user_id' => $this->owner->id,
            'channel_selected' => 'gofood',
            'image_path' => storage_path('app/test.jpg'),
            'image_hash' => 'dummyhash123',
            'image_bytes' => 100,
            'status' => \App\Enums\AiImportStatus::NEEDS_REVIEW,
            'platform_order_id' => 'GF-9999',
            'extracted' => [
                'channel' => 'gofood',
                'items' => [
                    ['name' => 'Legacy Item', 'qty' => 2, 'price' => 15000],
                ],
            ],
        ]);

        $res = $this->actingAs($this->owner)->get("/impor/{$import->id}");
        $res->assertStatus(200);
        $res->assertSee('Legacy Item');
    }

    public function test_destroy_import_deletes_record(): void
    {
        $import = \App\Models\AiImport::create([
            'user_id' => $this->owner->id,
            'channel_selected' => 'gofood',
            'image_path' => storage_path('app/test.jpg'),
            'image_hash' => 'dummyhash456',
            'image_bytes' => 100,
            'status' => \App\Enums\AiImportStatus::FAILED,
        ]);

        $res = $this->actingAs($this->owner)->delete("/impor/{$import->id}");
        $res->assertRedirect('/impor');
        $this->assertDatabaseMissing('ai_imports', ['id' => $import->id]);
    }
}
