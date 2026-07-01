<?php

namespace App\Http\Resources;

use App\Models\Payment;
use App\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status->value,
            'payment_method' => $this->payment_method->value,
            'amount' => Money::fromMinor($this->amount)->toFormatted(),
        ];
    }
}
