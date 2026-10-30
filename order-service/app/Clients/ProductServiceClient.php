<?php

namespace App\Clients;

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ProductServiceException;
use Illuminate\Support\Facades\Http;
use Throwable;

class ProductServiceClient
{
    private function baseUrl(): string
    {
        return rtrim(
            config('services.products.base_url', 'http://product-service:3000'),
            '/'
        );
    }

    public function getProduct(int $productId): array
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl().'/products/'.$productId);

            if ($response->status() === 404) {
                throw new ProductNotFoundException($productId);
            }

            if (! $response->successful()) {
                throw new ProductServiceException(
                    'Product Service gagal mengambil data produk. HTTP '
                    .$response->status()
                );
            }

            $product = $response->json('data');
            $id = is_array($product)
                ? filter_var($product['id'] ?? null, FILTER_VALIDATE_INT)
                : false;
            $stock = is_array($product)
                ? filter_var($product['stock'] ?? null, FILTER_VALIDATE_INT)
                : false;

            if (
                ! is_array($product)
                || $id === false
                || $id < 1
                || ! isset($product['name'])
                || ! is_string($product['name'])
                || trim($product['name']) === ''
                || ! isset($product['price'])
                || ! is_numeric($product['price'])
                || (float) $product['price'] < 0
                || $stock === false
                || $stock < 0
            ) {
                throw new ProductServiceException(
                    'Data produk dari Product Service tidak valid.'
                );
            }

            return $product;
        } catch (ProductNotFoundException|ProductServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ProductServiceException(
                'Product Service tidak dapat dihubungi.',
                $e
            );
        }
    }
}
