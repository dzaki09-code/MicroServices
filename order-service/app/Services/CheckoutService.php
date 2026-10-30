<?php

namespace App\Services;

use App\Clients\CartServiceClient;
use App\Clients\ProductServiceClient;
use App\Exceptions\CartServiceException;
use App\Exceptions\OrderBusinessException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
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
            throw new OrderBusinessException(
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
                throw new OrderBusinessException(
                    'Data item pada keranjang tidak valid.'
                );
            }

            $product = $this->productClient->getProduct($productId);

            if ($quantity > (int) $product['stock']) {
                throw new OrderBusinessException(
                    'Stok produk '.$product['name'].' tidak mencukupi.'
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
        } catch (CartServiceException $e) {
            Log::warning('Pesanan berhasil dibuat, tetapi keranjang gagal dikosongkan.', [
                'user_id' => $userId,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }
}
