<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserHasRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_gets_403_with_required_role_message_on_admin_page(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($kasir)->get('/products');

        $response->assertStatus(403);
        $response->assertSee('Halaman ini hanya untuk peran admin.', false);
    }

    public function test_admin_can_access_admin_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/products');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}