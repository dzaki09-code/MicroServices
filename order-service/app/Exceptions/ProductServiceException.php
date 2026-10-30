<?php

namespace App\Exceptions;

use RuntimeException;

class ProductServiceException extends RuntimeException
{
    public function __construct(
        string $message,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
