<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

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
}
