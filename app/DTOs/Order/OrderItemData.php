<?php

namespace App\DTOs\Order;

use App\ValueObjects\Money;

final class OrderItemData
{
    public function __construct(
        public readonly string $productName,
        public readonly int $quantity,
        public readonly Money $price,
    ) {}

    public function subtotal(): Money
    {
        return Money::fromMinor($this->price->toMinor() * $this->quantity);
    }
}
