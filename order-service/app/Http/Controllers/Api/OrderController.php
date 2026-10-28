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
}
