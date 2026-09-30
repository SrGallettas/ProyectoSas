<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseCashSessionRequest;
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
        ['cashSales' => $cashSales, 'cashRefunds' => $cashRefunds, 'inputs' => $inputs, 'outputs' => $outputs, 'expectedCash' => $expectedCash] = $session ? $this->summary($business, $session) : ['cashSales' => 0.0, 'cashRefunds' => 0.0, 'inputs' => 0.0, 'outputs' => 0.0, 'expectedCash' => 0.0];
        $closedSessions = $business->cashSessions()->where('status', CashSession::STATUS_CLOSED)->with(['openedBy', 'closedBy'])->latest('closed_at')->limit(20)->get();
        $legacyClosures = in_array($request->attributes->get('activeBusinessRole'), [Business::ROLE_OWNER, Business::ROLE_MANAGER], true)
            ? $business->cashClosures()->with('user')->latest('business_date')->limit(20)->get()
            : collect();

        return view('cash-sessions.index', compact('business', 'session', 'cashSales', 'cashRefunds', 'inputs', 'outputs', 'expectedCash', 'closedSessions', 'legacyClosures'));
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

    public function close(CloseCashSessionRequest $request): RedirectResponse
    {
        /** @var Business $business */
        $business = $request->attributes->get('activeBusiness');
        $session = $business->cashSessions()->where('status', CashSession::STATUS_OPEN)->with('movements')->first();
        if ($session === null) {
            throw ValidationException::withMessages(['closing_cash' => 'No hay ningún turno abierto.']);
        }
        $expectedCash = $this->summary($business, $session)['expectedCash'];
        $closingCash = (float) $request->validated('closing_cash');
        $session->update(['closed_by_user_id' => $request->user()->id, 'expected_cash' => $expectedCash, 'closing_cash' => $closingCash, 'difference' => $closingCash - $expectedCash, 'status' => CashSession::STATUS_CLOSED, 'closed_at' => now()]);
        AuditLog::record($request, $business, 'cash_session.closed', $session, null, $session->only(['expected_cash', 'closing_cash', 'difference']));

        return back()->with('status', 'Turno cerrado correctamente.');
    }

    /** @return array{cashSales: float, cashRefunds: float, inputs: float, outputs: float, expectedCash: float} */
    private function summary(Business $business, CashSession $session): array
    {
        $cashSales = (float) $business->sales()->completed()->where('payment_method', Sale::PAYMENT_CASH)->whereBetween('sold_at', [$session->opened_at, $session->closed_at ?? now()])->sum('total');
        $cashRefunds = (float) $business->refunds()->where('payment_method', Sale::PAYMENT_CASH)->whereBetween('refunded_at', [$session->opened_at, $session->closed_at ?? now()])->sum('total');
        $inputs = (float) $session->movements->where('type', CashMovement::TYPE_INPUT)->sum('amount');
        $outputs = (float) $session->movements->where('type', CashMovement::TYPE_OUTPUT)->sum('amount');

        return compact('cashSales', 'cashRefunds', 'inputs', 'outputs') + ['expectedCash' => (float) $session->opening_cash + $cashSales - $cashRefunds + $inputs - $outputs];
    }
}
