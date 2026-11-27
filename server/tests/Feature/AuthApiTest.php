<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_use_the_returned_token(): void
    {
        $user = User::factory()->create([
            'email' => 'lucas@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'lucas@example.com',
            'password' => 'secret-password',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id);

        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'lucas@example.com',
            'password' => 'secret-password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'lucas@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'email' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create([
            'email' => 'lucas@example.com',
            'password' => 'secret-password',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'lucas@example.com',
            'password' => 'secret-password',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Sessão encerrada com sucesso.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
