<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_successful_and_redirects(): void
    {
        $owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@test.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'owner@test.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_demo_login_as_owner(): void
    {
        $owner = User::create([
            'name' => 'Demo Owner',
            'email' => 'owner@demo.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $response = $this->post('/login/demo', ['role' => 'owner']);
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_demo_login_as_cashier(): void
    {
        $cashier = User::create([
            'name' => 'Demo Cashier',
            'email' => 'cashier@demo.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $response = $this->post('/login/demo', ['role' => 'cashier']);
        $response->assertRedirect('/kasir');
        $this->assertAuthenticatedAs($cashier);
    }

    public function test_demo_login_via_get_method(): void
    {
        $owner = User::create([
            'name' => 'Demo Owner GET',
            'email' => 'owner_get@demo.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $response = $this->get('/login/demo');
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_demo_login_via_get_method_with_cashier_role(): void
    {
        $cashier = User::create([
            'name' => 'Demo Cashier GET',
            'email' => 'cashier_get@demo.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $response = $this->get('/login/demo?role=cashier');
        $response->assertRedirect('/kasir');
        $this->assertAuthenticatedAs($cashier);
    }

    public function test_logout_via_get_method(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@test.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('secret'),
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $response = $this->get('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_cashier_restricted_from_owner_areas(): void
    {
        $cashier = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir@test.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Cashier can access POS
        $this->actingAs($cashier)->get('/kasir')->assertStatus(200);

        // Cashier is forbidden from Menu Master, Reports, and Settings (403)
        $this->actingAs($cashier)->get('/menu')->assertStatus(403);
        $this->actingAs($cashier)->get('/laporan')->assertStatus(403);
        $this->actingAs($cashier)->get('/pengaturan')->assertStatus(403);
    }
}
