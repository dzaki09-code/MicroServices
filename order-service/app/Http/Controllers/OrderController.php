<?php

namespace App\Http\Controllers;

use App\Exceptions\CartServiceException;
use App\Exceptions\OrderBusinessException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\OrderNotPayableException;
use App\Exceptions\ProductServiceException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UserOrdersRequest;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'order-service',
        ]);
    }

    public function index(OrderService $orderService): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $orderService->listOrders(),
            ]);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function store(
        StoreOrderRequest $request,
        CheckoutService $checkoutService
    ): JsonResponse {
        try {
            $order = $checkoutService->checkout(
                (int) $request->validated('user_id')
            );

            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'data' => $order,
            ], 201);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function show(int $id, OrderService $orderService): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Detail pesanan berhasil diambil.',
                'data' => $orderService->getOrder($id),
            ]);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function userOrders(
        UserOrdersRequest $request,
        int $userId,
        OrderService $orderService
    ): JsonResponse {
        try {
            $validated = $request->validated();

            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $orderService->getUserOrders(
                    $userId,
                    $validated['status'] ?? null
                ),
            ]);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function pay(int $id, OrderService $orderService): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Pembayaran berhasil.',
                'data' => $orderService->pay($id),
            ], 200);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    private function exceptionResponse(Throwable $exception): JsonResponse
    {
        if ($exception instanceof OrderNotFoundException) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        if ($exception instanceof OrderNotPayableException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'data' => $exception->order,
            ], 409);
        }

        if ($exception instanceof OrderBusinessException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'data' => null,
                'errors' => null,
            ], 409);
        }

        if ($exception instanceof ProductServiceException) {
            Log::error('Product Service gagal saat checkout.', [
                'service' => 'product-service',
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => $this->serviceErrorMessage($exception),
                'data' => null,
                'errors' => null,
            ], 503);
        }

        if ($exception instanceof CartServiceException) {
            return response()->json([
                'message' => $this->serviceErrorMessage($exception),
                'data' => null,
                'errors' => null,
            ], 503);
        }

        Log::error('Order API gagal memproses permintaan.', [
            'exception' => $exception,
        ]);

        return response()->json([
            'message' => config('app.debug')
                ? $exception->getMessage()
                : 'Terjadi kesalahan internal.',
            'data' => null,
            'errors' => null,
        ], 500);
    }

    private function serviceErrorMessage(Throwable $exception): string
    {
        return config('app.debug')
            ? $exception->getMessage()
            : 'Service terkait tidak dapat dihubungi, coba lagi nanti.';
    }
}
