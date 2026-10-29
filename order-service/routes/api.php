<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;

Route::get('/health', function () {
    return response()->json([
        'message' => 'Order Service berjalan dengan baik.',
        'data' => [
            'status' => 'ok',
        ],
    ]);
});

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/user/{userId}', [OrderController::class, 'userOrders']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::patch('/orders/{id}/pay', [OrderController::class, 'pay']);
