<?php

namespace App\Http\Controllers;

use App\Exceptions\CartServiceException;
use App\Exceptions\OrderBusinessException;
use App\Exceptions\ProductServiceException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UserOrdersRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $orders = Order::with('items')
                ->latest()
                ->get();

            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $orders,
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

    public function show(int $id): JsonResponse
    {
        try {
            $order = Order::with('items')->find($id);

            if (! $order) {
                return response()->json([
                    'message' => 'Pesanan tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'message' => 'Detail pesanan berhasil diambil.',
                'data' => $order,
            ]);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function userOrders(
        UserOrdersRequest $request,
        int $userId
    ): JsonResponse {
        try {
            $validated = $request->validated();

            $query = Order::with('items')
                ->where('user_id', $userId)
                ->latest();

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $query->get(),
            ]);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    public function pay(int $id): JsonResponse
    {
        try {
            $order = Order::with('items')->find($id);

            if (! $order) {
                return response()->json([
                    'message' => 'Pesanan tidak ditemukan.',
                    'data' => null,
                ], 404);
            }

            if ($order->status !== 'PENDING') {
                return response()->json([
                    'message' => 'Hanya pesanan berstatus PENDING yang dapat dibayar.',
                    'data' => $order,
                ], 409);
            }

            $order->update([
                'status' => 'PAID',
                'payment_status' => 'paid',
            ]);

            return response()->json([
                'message' => 'Pembayaran berhasil.',
                'data' => $order->fresh('items'),
            ], 200);
        } catch (Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    private function exceptionResponse(Throwable $exception): JsonResponse
    {
        if ($exception instanceof OrderBusinessException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'data' => null,
                'errors' => null,
            ], 409);
        }

        if ($exception instanceof ProductServiceException) {
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
