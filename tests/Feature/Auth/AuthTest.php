<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_and_user(): void
    {
        $payload = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'customer',
        ];

        $res = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/auth/register', $payload)
            ->assertCreated()
            ->assertJsonStructure(['message','data' => ['user' => ['id','name','email','role'],'token']]);

        $this->assertDatabaseHas('users', ['email' => 'tester@example.com']);
    }

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create(['email' => 'tester@example.com']);

        $res = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/auth/login', [
                'email' => 'tester@example.com',
                'password' => 'password',
            ])
            ->assertOk()
            ->assertJsonStructure(['message','data' => ['user' => ['id','name','email','role'],'token']]);
    }
}

