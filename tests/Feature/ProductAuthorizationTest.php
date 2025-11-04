<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_create_product(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Item A',
            'sku' => 'SKU-A-'.uniqid(),
            'price' => 10.50,
            'stock' => 5,
        ];

        $this->postJson('/api/products', $payload)
            ->assertStatus(403)
            ->assertJson([
                'message' => 'Forbidden: admin access required',
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Item B',
            'sku' => 'SKU-B-'.uniqid(),
            'price' => 20.00,
            'stock' => 10,
        ];

        $this->postJson('/api/products', $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id','name','sku','price','stock','created_at','updated_at'],
            ]);
    }
}

