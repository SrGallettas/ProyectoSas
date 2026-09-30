<?php

namespace App\Models;

use Database\Factories\CashClosureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'business_date', 'total_revenue', 'expected_cash', 'card_revenue', 'counted_cash', 'difference', 'ticket_count', 'closed_at'])]
class CashClosure extends Model
{
    /** @use HasFactory<CashClosureFactory> */
    use HasFactory;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'total_revenue' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'card_revenue' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'difference' => 'decimal:2',
            'ticket_count' => 'integer',
            'closed_at' => 'datetime',
        ];
    }
}
