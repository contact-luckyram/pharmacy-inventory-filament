<?php

namespace App\Exceptions;

use App\Models\Medicine;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(Medicine $medicine, int $requested, int $available)
    {
        parent::__construct(
            "Not enough stock for {$medicine->displayName()}: requested {$requested}, available {$available}."
        );
    }
}
