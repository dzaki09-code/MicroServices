<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

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
Route::get('/orders/user/{userId}', [OrderController::class, 'userOrders'])
    ->whereNumber('userId');
Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
Route::patch('/orders/{id}/pay', [OrderController::class, 'pay'])->whereNumber('id');
