<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_plain_text_token_stored_as_a_hash(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@pos.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'kasir@pos.test',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);

        $plainTextToken = $response->json('token');
        [$tokenId, $tokenSecret] = explode('|', $plainTextToken, 2);

        $this->assertSame('1', $tokenId);
        $this->assertNotEmpty($tokenSecret);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
            'tokenable_id' => $user->id,
            'token' => hash('sha256', $tokenSecret),
        ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'kasir@pos.test',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'kasir@pos.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()->assertExactJson([
            'message' => 'Kredensial tidak valid.',
        ]);

        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }
}
