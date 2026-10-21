<?php

namespace App\Services;

use illuminate\Support\Facades\Http;
use illuminate\Support\Facades\Log;
use Throwable;  

class ProductService
{
    public function checkProduct(int $productId): ?array
    {
        try {
            $response = Http::baseUrl(config('services.products.url'))
                ->get("/products/{$productId}");

            if ($response->successful() && isset($response->json()['data'])) {
                return $response->json()['data']    ;
            }

           return null;
        } catch (Throwable $e) {
            Log::error('Gagal mengambil data produk', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}