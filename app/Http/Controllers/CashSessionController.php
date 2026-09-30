<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpenCashSessionRequest;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashSessionController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $session = $business->cashSessions()->where('status', CashSession::STATUS_OPEN)->with(['openedBy', 'movements.user'])->first();
        $cashSales = $session ? (float) $business->sales()->where('payment_method', Sale::PAYMENT_CASH)->where('sold_at', '>=', $session->opened_at)->sum('total') : 0.0;
        $inputs = $session ? (float) $session->movements->where('type', CashMovement::TYPE_INPUT)->sum('amount') : 0.0;
        $outputs = $session ? (float) $session->movements->where('type', CashMovement::TYPE_OUTPUT)->sum('amount') : 0.0;
        $expectedCash = $session ? (float) $session->opening_cash + $cashSales + $inputs - $outputs : 0.0;

        return view('cash-sessions.index', compact('business', 'session', 'cashSales', 'inputs', 'outputs', 'expectedCash'));
    }

    public function store(OpenCashSessionRequest $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $session = DB::transaction(function () use ($business, $request): CashSession {
            if ($business->cashSessions()->where('status', CashSession::STATUS_OPEN)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['opening_cash' => 'Ya existe un turno de caja abierto.']);
            }

            return $business->cashSessions()->create(['opened_by_user_id' => $request->user()->id, 'opening_cash' => $request->validated('opening_cash'), 'status' => CashSession::STATUS_OPEN, 'opened_at' => now()]);
        });
        AuditLog::record($request, $business, 'cash_session.opened', $session, null, ['opening_cash' => $session->opening_cash]);

        return back()->with('status', 'Turno de caja abierto.');
    }
}
