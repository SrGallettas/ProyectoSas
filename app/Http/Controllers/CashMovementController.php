<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashMovementRequest;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CashMovementController extends Controller
{
    public function store(StoreCashMovementRequest $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $session = $business->cashSessions()->where('status', CashSession::STATUS_OPEN)->first();
        if ($session === null) {
            throw ValidationException::withMessages(['amount' => 'No hay ningún turno de caja abierto.']);
        }
        $movement = $session->movements()->create([...$request->validated(), 'user_id' => $request->user()->id, 'occurred_at' => now()]);
        AuditLog::record($request, $business, 'cash_movement.created', $movement, null, $movement->only(['type', 'amount', 'reason']));

        return back()->with('status', 'Movimiento de caja registrado.');
    }
}
