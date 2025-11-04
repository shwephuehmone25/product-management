<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        Product::factory()->count(5)->create();
    }

    public function test_products_list_returns_paginated_data(): void
    {
        Sanctum::actingAs($this->customer);
        $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/products?per_page=3')
            ->assertOk()
            ->assertJsonStructure(['data','meta' => ['current_page','per_page','total','last_page']]);
    }

    public function test_admin_can_create_product(): void
    {
        Sanctum::actingAs($this->admin);
        $payload = [
            'name' => 'New Product',
            'sku' => 'SKU-NEW',
            'price' => 12.34,
            'stock' => 10,
        ];
        $this->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/products', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Product');

        $this->assertDatabaseHas('products', ['sku' => 'SKU-NEW']);
    }

    public function test_filters_by_q_and_price(): void
    {
        Product::factory()->create(['name' => 'Blue Shirt', 'sku' => 'TS-001', 'price' => 19.99]);
        Product::factory()->create(['name' => 'Green Pants', 'sku' => 'PA-009', 'price' => 49.99]);

        Sanctum::actingAs($this->customer);
        $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/products?q=shirt&max_price=20')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
