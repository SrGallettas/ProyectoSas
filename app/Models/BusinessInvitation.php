<?php

namespace App\Models;

use Database\Factories\BusinessInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['email', 'role', 'token', 'expires_at', 'accepted_at'])]
class BusinessInvitation extends Model
{
    /** @use HasFactory<BusinessInvitationFactory> */
    use HasFactory;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }
}
