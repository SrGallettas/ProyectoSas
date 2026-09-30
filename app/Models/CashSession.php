<?php

namespace App\Models;

use Database\Factories\CashSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['opened_by_user_id', 'closed_by_user_id', 'opening_cash', 'expected_cash', 'closing_cash', 'difference', 'status', 'opened_at', 'closed_at'])]
class CashSession extends Model
{
    /** @use HasFactory<CashSessionFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    protected function casts(): array
    {
        return ['opening_cash' => 'decimal:2', 'expected_cash' => 'decimal:2', 'closing_cash' => 'decimal:2', 'difference' => 'decimal:2', 'opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }
}
