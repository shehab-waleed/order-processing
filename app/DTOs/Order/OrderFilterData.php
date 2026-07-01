<?php

namespace App\DTOs\Order;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;

final class OrderFilterData
{
    public function __construct(
        public readonly ?OrderStatus $status,
        public readonly int $perPage,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $status = $request->string('status')->toString();

        return new self(
            status: $status !== '' ? OrderStatus::from($status) : null,
            perPage: $request->integer('per_page', 15),
        );
    }
}
