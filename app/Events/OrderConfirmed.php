<?php

namespace App\Events;

use App\Models\Order;

class OrderConfirmed
{
    public function __construct(public readonly Order $order)
    {
    }
}

