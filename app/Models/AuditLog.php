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

    public function actionLabel(): string
    {
        return match ($this->action) {
            'invitation.created' => 'Invitación creada',
            'invitation.regenerated' => 'Invitación regenerada',
            'invitation.cancelled' => 'Invitación cancelada',
            'member.updated' => 'Acceso modificado',
            'cash_closure.created' => 'Cierre diario creado',
            'cash_closure.corrected' => 'Cierre diario corregido',
            'cash_session.opened' => 'Turno de caja abierto',
            'cash_session.closed' => 'Turno de caja cerrado',
            'cash_movement.created' => 'Movimiento de caja',
            'sale.voided' => 'Venta anulada',
            'refund.created' => 'Devolución registrada',
            default => 'Operación registrada',
        };
    }

    /** @return list<string> */
    public function changeSummary(): array
    {
        $before = $this->before ?? [];
        $after = $this->after ?? [];

        return match ($this->action) {
            'invitation.created', 'invitation.cancelled' => [($after['email'] ?? $before['email'] ?? 'Correo no disponible').' · '.$this->roleLabel($after['role'] ?? $before['role'] ?? null)],
            'invitation.regenerated' => ['Se generó un enlace nuevo para '.($after['email'] ?? 'la invitación').'.'],
            'member.updated' => $this->memberChanges($before, $after),
            'cash_closure.created', 'cash_closure.corrected' => ['Contado: '.$this->money($after['counted_cash'] ?? null).' · Diferencia: '.$this->money($after['difference'] ?? null)],
            'cash_session.opened' => ['Fondo inicial: '.$this->money($after['opening_cash'] ?? null)],
            'cash_session.closed' => ['Esperado: '.$this->money($after['expected_cash'] ?? null).' · Contado: '.$this->money($after['closing_cash'] ?? null).' · Diferencia: '.$this->money($after['difference'] ?? null)],
            'cash_movement.created' => [($after['type'] ?? null) === CashMovement::TYPE_INPUT ? 'Entrada de '.$this->money($after['amount'] ?? null) : 'Salida de '.$this->money($after['amount'] ?? null), 'Motivo: '.($after['reason'] ?? 'No indicado')],
            'sale.voided' => ['Importe: '.$this->money($after['total'] ?? null), 'Motivo: '.($after['reason'] ?? 'No indicado')],
            'refund.created' => ['Ticket #'.($after['sale_id'] ?? '—').' · '.$this->money($after['total'] ?? null).' · '.$this->paymentLabel($after['payment_method'] ?? null), 'Motivo: '.($after['reason'] ?? 'No indicado')],
            default => ['Los detalles técnicos están disponibles en la base de datos.'],
        };
    }

    /** @param array<string, mixed> $before @param array<string, mixed> $after @return list<string> */
    private function memberChanges(array $before, array $after): array
    {
        $changes = [];
        if (($before['role'] ?? null) !== ($after['role'] ?? null)) {
            $changes[] = 'Rol: '.$this->roleLabel($before['role'] ?? null).' → '.$this->roleLabel($after['role'] ?? null);
        }
        if ((bool) ($before['is_active'] ?? false) !== (bool) ($after['is_active'] ?? false)) {
            $changes[] = (bool) ($after['is_active'] ?? false) ? 'Acceso activado.' : 'Acceso desactivado.';
        }

        return $changes ?: ['No hubo cambios efectivos.'];
    }

    private function roleLabel(?string $role): string
    {
        return ['owner' => 'Propietario', 'manager' => 'Encargado', 'staff' => 'Camarero'][$role] ?? 'Rol desconocido';
    }

    private function paymentLabel(?string $method): string
    {
        return $method === Sale::PAYMENT_CARD ? 'Tarjeta' : 'Efectivo';
    }

    private function money(mixed $amount): string
    {
        return $amount === null ? '—' : number_format((float) $amount, 2, ',', '.').' €';
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
