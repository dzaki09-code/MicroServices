<?php

namespace App\Exceptions;

class InvalidCartException extends OrderBusinessException
{
    public function __construct(string $message = 'Data item pada keranjang tidak valid.')
    {
        parent::__construct($message);
    }
}
