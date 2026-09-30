<?php

namespace App\Models;

use Database\Factories\AdjustmentRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_id', 'requested_by_user_id', 'reviewed_by_user_id', 'type', 'payload', 'status', 'review_note', 'reviewed_at'])]
class AdjustmentRequest extends Model
{
    /** @use HasFactory<AdjustmentRequestFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    protected function casts(): array
    {
        return ['payload' => 'array', 'reviewed_at' => 'datetime'];
    }
}
