<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use LogicException;

#[Fillable(['user_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'ip_address'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Request $request, Business $business, string $action, ?Model $subject = null, ?array $before = null, ?array $after = null): self
    {
        return $business->auditLogs()->create(['user_id' => $request->user()?->id, 'action' => $action, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'before' => $before, 'after' => $after, 'ip_address' => $request->ip()]);
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Los registros de auditoría no se pueden modificar.'));
        static::deleting(fn (): never => throw new LogicException('Los registros de auditoría no se pueden eliminar.'));
    }

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
    }
}
