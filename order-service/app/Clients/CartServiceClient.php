<?php

namespace App\Clients;

use App\Exceptions\CartServiceException;
use Illuminate\Support\Facades\Http;
use Throwable;

class CartServiceClient
{
    private function baseUrl(): string
    {
        return rtrim(
            config('services.cart.base_url', 'http://cart-service:3001'),
            '/'
        );
    }

    public function getCart(int $userId): array
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl() . '/carts/' . $userId);

            if (!$response->successful()) {
                throw new CartServiceException(
                    'Cart Service gagal mengambil keranjang. HTTP '
                    . $response->status()
                );
            }

            $cart = $response->json('data');

            if (
                !is_array($cart)
                || !isset($cart['user_id'])
                || !isset($cart['items'])
                || !is_array($cart['items'])
            ) {
                throw new CartServiceException(
                    'Format respons keranjang tidak valid.'
                );
            }

            return $cart;
        } catch (CartServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CartServiceException(
                'Cart Service tidak dapat dihubungi.',
                0,
                $e
            );
        }
    }

    public function clearCart(int $userId): void
    {
        try {
            $response = Http::timeout(5)
                ->delete($this->baseUrl() . '/carts/' . $userId);

            if (!$response->successful()) {
                throw new CartServiceException(
                    'Cart Service gagal mengosongkan keranjang. HTTP '
                    . $response->status()
                );
            }
        } catch (CartServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CartServiceException(
                'Cart Service tidak dapat mengosongkan keranjang.',
                0,
                $e
            );
        }
    }
}
