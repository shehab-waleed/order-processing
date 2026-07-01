<?php

namespace App\Http\Resources;

use App\Models\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'price_per_item' => Money::fromMinor($this->price_per_item)->toFormatted(),
            'subtotal' => Money::fromMinor($this->subtotal())->toFormatted(),
        ];
    }
}
