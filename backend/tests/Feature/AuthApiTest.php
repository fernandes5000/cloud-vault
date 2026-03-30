<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_fetch_profile_and_logout(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'preferred_locale' => 'pt_BR',
            'timezone' => 'America/Sao_Paulo',
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('user.email', 'maria@example.com')
            ->assertJsonPath('user.preferredLocale', 'pt_BR')
            ->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'preferred_locale' => 'pt_BR',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'maria@example.com',
            'password' => 'StrongPass123!',
            'device_name' => 'test-suite',
        ]);

        $token = $loginResponse->json('token');

        $loginResponse->assertOk()
            ->assertJsonPath('user.email', 'maria@example.com');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('email', 'maria@example.com');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();
    }

    public function test_login_is_rejected_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'StrongPass123!',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }
}
