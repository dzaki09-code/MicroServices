<?php

namespace Tests\Feature;

use App\Clients\CartServiceClient;
use App\Clients\ProductServiceClient;
use App\Exceptions\CartServiceException;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_service_status(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_invalid_checkout_returns_validation_errors(): void
    {
        $this->postJson('/api/orders', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['user_id']]);
    }

    public function test_empty_cart_returns_bad_request(): void
    {
        $this->mock(CartServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCart')
                ->once()
                ->with(42)
                ->andReturn(['user_id' => 42, 'items' => []]);
        });

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertStatus(400)
            ->assertJsonPath('data', null)
            ->assertJsonPath('message', 'Keranjang kosong. Tambahkan produk sebelum checkout.')
            ->assertJsonPath('errors', null);
    }

    public function test_unavailable_cart_service_returns_service_unavailable(): void
    {
        $this->mock(CartServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCart')
                ->once()
                ->andThrow(new CartServiceException('connection failed'));
        });

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'connection failed')
            ->assertJsonPath('data', null)
            ->assertJsonPath('errors', null);
    }

    public function test_successful_checkout_keeps_order_when_cart_cannot_be_cleared(): void
    {
        $this->mock(CartServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCart')
                ->once()
                ->with(42)
                ->andReturn([
                    'user_id' => 42,
                    'items' => [
                        ['product_id' => 9, 'quantity' => 2],
                    ],
                ]);
            $mock->shouldReceive('clearCart')
                ->once()
                ->with(42)
                ->andThrow(new CartServiceException('service unavailable'));
        });

        $this->mock(ProductServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getProduct')
                ->once()
                ->with(9)
                ->andReturn([
                    'id' => 9,
                    'name' => 'Product',
                    'price' => 12.5,
                    'stock' => 3,
                ]);
        });

        Log::shouldReceive('warning')->once();

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertCreated()
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.items.0.quantity', 2);

        $this->assertDatabaseHas('orders', [
            'user_id' => 42,
            'status' => 'PENDING',
        ]);
        $this->assertDatabaseCount('order_items', 1);
    }

    public function test_product_not_found_returns_bad_request(): void
    {
        $this->mock(CartServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCart')
                ->once()
                ->andReturn([
                    'user_id' => 42,
                    'items' => [
                        ['product_id' => 9, 'quantity' => 1],
                    ],
                ]);
        });

        Http::fake([
            '*' => Http::response(['message' => 'Product not found'], 404),
        ]);

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Produk dengan ID 9 tidak ditemukan.')
            ->assertJsonPath('data', null);
    }

    public function test_insufficient_stock_returns_bad_request(): void
    {
        $this->mock(CartServiceClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCart')
                ->once()
                ->andReturn([
                    'user_id' => 42,
                    'items' => [
                        ['product_id' => 9, 'quantity' => 2],
                    ],
                ]);
        });

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'id' => 9,
                    'name' => 'Product',
                    'price' => 12.5,
                    'stock' => 1,
                ],
            ]),
        ]);

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Stok produk Product tidak mencukupi.')
            ->assertJsonPath('data', null);
    }

    public function test_invalid_order_status_filter_returns_validation_errors(): void
    {
        $this->getJson('/api/orders/user/42?status=unknown')
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['status']]);
    }

    public function test_missing_order_returns_not_found(): void
    {
        $this->getJson('/api/orders/999')
            ->assertNotFound()
            ->assertJsonPath('data', null);
    }

    public function test_payment_changes_pending_order_to_paid_and_rejects_repayment(): void
    {
        $order = Order::create([
            'user_id' => 42,
            'total_amount' => 25,
            'status' => 'PENDING',
            'payment_status' => 'unpaid',
        ]);

        $this->patchJson("/api/orders/{$order->id}/pay")
            ->assertOk()
            ->assertJsonPath('data.status', 'PAID');

        $this->patchJson("/api/orders/{$order->id}/pay")
            ->assertStatus(409)
            ->assertJsonPath('data.status', 'PAID');
    }

    public function test_unexpected_api_errors_do_not_expose_internal_details(): void
    {
        $this->mock(CheckoutService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('checkout')
                ->once()
                ->andThrow(new \RuntimeException('private database detail'));
        });

        $this->postJson('/api/orders', ['user_id' => 42])
            ->assertInternalServerError()
            ->assertJsonPath('message', 'Terjadi kesalahan internal.')
            ->assertJsonPath('data', null)
            ->assertDontSee('private database detail');
    }
}
