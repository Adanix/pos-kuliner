<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
