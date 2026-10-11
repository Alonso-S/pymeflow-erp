<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_screen(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('PymeFlow ERP');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@pymeflow.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@pymeflow.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@pymeflow.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@pymeflow.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'inactivo@pymeflow.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactivo@pymeflow.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
