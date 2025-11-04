<?php

namespace Tests\Feature\Order;

use App\Mail\OrderConfirmedMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        Product::factory()->create(['id' => 1, 'price' => 49.99]);
        Product::factory()->create(['id' => 3, 'price' => 49.99]);
    }

    public function test_customer_can_create_order_and_totals_calculated(): void
    {
        Sanctum::actingAs($this->customer);

        $payload = [
            'status' => 'pending',
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
                ['product_id' => 3, 'quantity' => 1],
            ],
        ];

        $this->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.total_amount', '149.97')
            ->assertJsonCount(2, 'data.items');
    }

    public function test_admin_update_status_queues_mail(): void
    {
        // Create order as customer first
        Sanctum::actingAs($this->customer);
        $orderId = $this->postJson('/api/orders', [
            'status' => 'pending',
            'items' => [
                ['product_id' => 1, 'quantity' => 2],
            ],
        ])->json('data.id');

        // Confirm as admin and assert mail queued
        Mail::fake();
        Sanctum::actingAs($this->admin);
        $this->withHeaders(['Accept' => 'application/json'])
            ->putJson("/api/orders/{$orderId}/status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        Mail::assertQueued(OrderConfirmedMail::class);
    }

    public function test_customer_cannot_update_status(): void
    {
        Sanctum::actingAs($this->customer);
        $orderId = $this->postJson('/api/orders', [
            'status' => 'pending',
            'items' => [
                ['product_id' => 1, 'quantity' => 1],
            ],
        ])->json('data.id');

        $this->putJson("/api/orders/{$orderId}/status", ['status' => 'confirmed'])
            ->assertStatus(403);
    }
}
