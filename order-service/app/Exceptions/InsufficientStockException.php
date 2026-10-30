<?php

namespace App\Exceptions;

class InsufficientStockException extends OrderBusinessException
{
    public function __construct(
        string $productName,
        int $requested,
        int $available
    ) {
        parent::__construct(
            "Stok {$productName} tidak mencukupi: diminta {$requested}, tersedia {$available}."
        );
    }
}
