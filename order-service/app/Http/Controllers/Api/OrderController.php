<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index():JsonResponse
    {
        $orders = Order::query()
        ->latest()
        ->get();

        return response()->json([
            'data'=> $orders,
        ]);
    }

    public function store(Request $request):JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'min:1'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:pending,processing,completed,cancelled'],
        'payment_status' => ['sometimes', 'in:unpaid,paid,refunded'],
        ]);

        $order = Order::create($validated);

        return response()->json([
            'message' => 'Pesanan berhasil ditambahkan.',
            'data' => $order,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan Tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ]);
    }

    public function userOrders(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'sometimes',
                'required',
                'string',
                'in:PENDING,PAID',
            ],
        ]);

        $query = Order::query()
            ->where('user_id', $userId)
            ->with('items')
            ->latest();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil.',
            'data' => $query->get(),
        ]);
    }
}
