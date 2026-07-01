<?php

namespace App\DTOs\Order;

use App\ValueObjects\Money;
use Illuminate\Http\Request;

final class OrderData
{
    /**
     * @param  array<int, OrderItemData>  $items
     */
    public function __construct(
        public readonly array $items,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $items = $request->collect('items')->keys()->map(
            fn (int|string $index): OrderItemData => new OrderItemData(
                productName: $request->string("items.{$index}.product_name")->toString(),
                quantity: $request->integer("items.{$index}.quantity"),
                price: Money::fromMajor($request->float("items.{$index}.price")),
            ),
        )->all();

        return new self($items);
    }

    public function total(): Money
    {
        return array_reduce(
            $this->items,
            fn (Money $carry, OrderItemData $item): Money => $carry->add($item->subtotal()),
            Money::fromMinor(0),
        );
    }
}
