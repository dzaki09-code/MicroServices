<?php

namespace App\Services;

use App\Exceptions\OrderNotFoundException;
use App\Exceptions\OrderNotPayableException;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function listOrders(): Collection
    {
        return Order::with('items')->latest()->get();
    }

    public function getOrder(int $id): Order
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            throw new OrderNotFoundException($id);
        }

        return $order;
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
                ->find($id);

            if (! $order) {
                throw new OrderNotFoundException($id);
            }

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
