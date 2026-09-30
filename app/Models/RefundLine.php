<?php

namespace App\Models;

use Database\Factories\RefundLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_line_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'line_total'])]
class RefundLine extends Model
{
    /** @use HasFactory<RefundLineFactory> */
    use HasFactory;

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function saleLine(): BelongsTo
    {
        return $this->belongsTo(SaleLine::class);
    }

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }
}
