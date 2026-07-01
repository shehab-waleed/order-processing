<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    // TODO Convert to state machine ( Using state pattern ) if we have more states with complex logic
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => $target === self::Cancelled,
            self::Cancelled => false,
        };
    }
}
