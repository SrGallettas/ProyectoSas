<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'voided_by_user_id', 'customer_id', 'total', 'payment_method', 'checkout_token', 'sold_at', 'void_reason', 'voided_at'])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    public const PAYMENT_CARD = 'card';

    public const PAYMENT_CASH = 'cash';

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by_user_id');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->whereNull('voided_at');
    }

    /** @return HasMany<SaleLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            self::PAYMENT_CARD => 'Tarjeta',
            default => 'Efectivo',
        };
    }

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'sold_at' => 'datetime', 'voided_at' => 'datetime'];
    }
}
