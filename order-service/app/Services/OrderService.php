<?php

namespace App\Services;

use App\Clients\CartServiceClient;
use App\Clients\ProductServiceClient;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidCartException;
use App\Exceptions\OrderNotPayableException;
use App\Exceptions\ServiceUnavailableException;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private CartServiceClient $cartClient,
        private ProductServiceClient $productClient
    ) {}

    public function checkout(int $userId): Order
    {
        $cart = $this->cartClient->getCart($userId);
        $items = $cart['items'];

        if (empty($items)) {
            throw new InvalidCartException(
                'Keranjang kosong. Tambahkan produk sebelum checkout.'
            );
        }

        $products = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $productId = filter_var(
                $item['product_id'] ?? null,
                FILTER_VALIDATE_INT
            );
            $quantity = filter_var(
                $item['quantity'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1) {
                throw new InvalidCartException;
            }

            $product = $this->productClient->getProduct($productId);

            if ($quantity > (int) $product['stock']) {
                throw new InsufficientStockException(
                    (string) $product['name'],
                    $quantity,
                    (int) $product['stock']
                );
            }

            $unitPrice = (float) $product['price'];
            $subtotal = round($unitPrice * $quantity, 2);

            $products[] = [
                'product_id' => (int) $product['id'],
                'product_name' => (string) $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            $totalAmount = round($totalAmount + $subtotal, 2);
        }

        $order = DB::transaction(function () use (
            $userId,
            $products,
            $totalAmount
        ) {
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
                'payment_status' => 'unpaid',
            ]);

            foreach ($products as $product) {
                $order->items()->create($product);
            }

            return $order->load('items');
        });

        try {
            $this->cartClient->clearCart($userId);
        } catch (ServiceUnavailableException $e) {
            Log::warning('Pesanan berhasil dibuat, tetapi keranjang gagal dikosongkan.', [
                'service' => $e->service,
                'operation' => $e->operation,
                'user_id' => $userId,
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            throw $e;
        }

        return $order;
    }

    public function listOrders(): Collection
    {
        return Order::with('items')->latest()->get();
    }

    public function getOrder(int $id): Order
    {
        return Order::with('items')->findOrFail($id);
    }

    public function getUserOrders(int $userId, ?string $status): Collection
    {
        $query = Order::with('items')
            ->where('user_id', $userId)
            ->latest();

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function pay(int $id): Order
    {
        return DB::transaction(function () use ($id): Order {
            $order = Order::with('items')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($order->status !== 'PENDING') {
                throw new OrderNotPayableException($order);
            }

            $order->update([
                'status' => 'PAID',
                'payment_status' => 'paid',
            ]);

            return $order->fresh('items');
        });
    }
}
