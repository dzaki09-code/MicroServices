<?php

namespace App\Exceptions;

class ProductNotFoundException extends OrderBusinessException
{
    public function __construct(int $productId)
    {
        parent::__construct(
            "Produk dengan ID {$productId} tidak ditemukan."
        );
    }
}
