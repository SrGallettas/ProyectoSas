<?php

namespace App\Models;

use Database\Factories\RefundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['business_id', 'sale_id', 'user_id', 'total', 'payment_method', 'reason', 'refunded_at'])]
class Refund extends Model
{
    /** @use HasFactory<RefundFactory> */
    use HasFactory;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(RefundLine::class);
    }

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'refunded_at' => 'datetime'];
    }
}
