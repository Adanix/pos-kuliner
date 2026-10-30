<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('POS Kuliner');
    }

    public function test_docs_page_loads_and_contains_extensibility_note(): void
    {
        $response = $this->get('/docs');
        $response->assertStatus(200);
        $response->assertSee('web apps ini masih bisa di kembangkan sesuai kebutuhan');
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_reports_export_downloads_xlsx_file(): void
    {
        $owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@export.test',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/laporan/export?period=today');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_transaction_number_sequence_increments_uniquely(): void
    {
        $salesService = app(SalesService::class);
        $num1 = $salesService->generateTransactionNumber();
        $num2 = $salesService->generateTransactionNumber();
        $num3 = $salesService->generateTransactionNumber();

        $this->assertNotEquals($num1, $num2);
        $this->assertNotEquals($num2, $num3);
        $this->assertStringStartsWith('CIO-', $num1);
    }

    public function test_menu_image_upload_and_storage_path(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $owner = User::create([
            'name' => 'Owner Menu Test',
            'email' => 'owner@menu.test',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $category = \App\Models\Category::create(['name' => 'Minuman', 'sort_order' => 1, 'is_active' => true]);

        $rawJpg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        $tmp = tempnam(sys_get_temp_dir(), 'test_jpg');
        file_put_contents($tmp, $rawJpg);
        $file = new \Illuminate\Http\UploadedFile($tmp, 'menu_foto.jpg', 'image/jpeg', null, true);

        $response = $this->actingAs($owner)->post('/menu', [
            'category_id' => $category->id,
            'name' => 'Es Cendol Durian',
            'price' => '25000',
            'description' => 'Segar dan manis',
            'image' => $file,
        ]);

        $response->assertRedirect('/menu');

        $menu = \App\Models\Menu::where('name', 'Es Cendol Durian')->first();
        $this->assertNotNull($menu);
        $this->assertStringStartsWith('/storage/menus/', $menu->image_path);

        $relativeStoragePath = str_replace('/storage/', '', $menu->image_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($relativeStoragePath);
    }

    public function test_menu_and_ingredient_resource_routes(): void
    {
        $owner = User::create([
            'name' => 'Owner Res Test',
            'email' => 'owner@res.test',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $category = \App\Models\Category::create(['name' => 'Makanan', 'sort_order' => 1, 'is_active' => true]);
        $menu = \App\Models\Menu::create(['category_id' => $category->id, 'name' => 'Mie Goreng', 'price' => 15000, 'is_active' => true]);
        $ingredient = \App\Models\Ingredient::create(['name' => 'Tepung Terigu', 'base_unit' => 'g', 'is_active' => true]);

        // GET /menu/{menu} redirects to /menu
        $this->actingAs($owner)->get("/menu/{$menu->id}")->assertRedirect('/menu');

        // GET /menu/{menu} as JSON returns json data
        $this->actingAs($owner)->getJson("/menu/{$menu->id}")->assertStatus(200)->assertJsonPath('name', 'Mie Goreng');

        // GET /bahan/{bahan} redirects to /bahan
        $this->actingAs($owner)->get("/bahan/{$ingredient->id}")->assertRedirect('/bahan');

        // GET /bahan/{bahan} as JSON returns json data
        $this->actingAs($owner)->getJson("/bahan/{$ingredient->id}")->assertStatus(200)->assertJsonPath('name', 'Tepung Terigu');
    }
}
