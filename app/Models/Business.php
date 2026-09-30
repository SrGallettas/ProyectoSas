<?php

namespace App\Models;

use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Business extends Model
{
    public const ROLE_OWNER = 'owner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_STAFF = 'staff';

    /** @use HasFactory<BusinessFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** @return HasMany<Category, $this> */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /** @return HasMany<Sale, $this> */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /** @return HasMany<CashClosure, $this> */
    public function cashClosures(): HasMany
    {
        return $this->hasMany(CashClosure::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role', 'is_active'])->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(BusinessInvitation::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function cashSessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function adjustmentRequests(): HasMany
    {
        return $this->hasMany(AdjustmentRequest::class);
    }
}
