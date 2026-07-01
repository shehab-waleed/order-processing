<?php

namespace App\DTOs\Payment;

use App\Enums\PaymentStatus;
use Illuminate\Http\Request;

final class PaymentFilterData
{
    public function __construct(
        public readonly ?PaymentStatus $status,
        public readonly int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $status = $request->string('status')->toString();

        return new self(
            status: $status !== '' ? PaymentStatus::from($status) : null,
            perPage: $request->integer('per_page', 15),
        );
    }
}
