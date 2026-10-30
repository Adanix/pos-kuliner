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
}
