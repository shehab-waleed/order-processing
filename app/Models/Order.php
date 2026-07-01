<?php

namespace App\Models;

use App\DTOs\Order\OrderData;
use App\DTOs\Order\OrderItemData;
use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['user_id', 'status', 'total'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    public static function place(User $user, OrderData $data): self
    {
        $order = new self([
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => $data->total()->toMinor(),
        ]);

        DB::transaction(function () use ($order, $data): void {
            $order->save();
            $order->storeItems($data);
        });

        return $order;
    }

    public function replaceItems(OrderData $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->items()->delete();
            $this->storeItems($data);
            $this->update(['total' => $data->total()->toMinor()]);
        });
    }

    public function storeItems(OrderData $data): void
    {
        $this->items()->createMany(
            array_map(
                fn (OrderItemData $item): array => [
                    'product_name' => $item->productName,
                    'quantity' => $item->quantity,
                    'price_per_item' => $item->price->toMinor(),
                ],
                $data->items,
            ),
        );
    }

    public function transitionTo(OrderStatus $status): void
    {
        $this->update(['status' => $status]);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    public function hasPayments(): bool
    {
        return $this->payments()->exists();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'integer',
        ];
    }
}
