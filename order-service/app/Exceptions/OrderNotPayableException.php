<?php

namespace App\Exceptions;

use App\Models\Order;

class OrderNotPayableException extends OrderBusinessException
{
    public function __construct(public readonly Order $order)
    {
        parent::__construct(
            'Hanya pesanan berstatus PENDING yang dapat dibayar.'
        );
    }
}
