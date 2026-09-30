<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashClosureRequest;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashClosureController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $selectedDate = $this->selectedDate($request->string('date')->toString());
        $summary = $this->dailySummary($activeBusiness, $selectedDate);
        $existingClosure = $activeBusiness->cashClosures()->with('user')->whereDate('business_date', $selectedDate)->first();
        $closures = $activeBusiness->cashClosures()->with('user')->latest('business_date')->paginate(20);

        return view('cash-closures.index', compact('activeBusiness', 'closures', 'existingClosure', 'selectedDate', 'summary'));
    }

    public function store(StoreCashClosureRequest $request): RedirectResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $selectedDate = CarbonImmutable::createFromFormat('Y-m-d', $request->validated('business_date'))->startOfDay();
        $summary = $this->dailySummary($activeBusiness, $selectedDate);
        $countedCash = (float) $request->validated('counted_cash');

        $closure = $activeBusiness->cashClosures()
            ->whereDate('business_date', $selectedDate)
            ->firstOrNew();
        $wasExisting = $closure->exists;
        $before = $wasExisting ? $closure->only(['counted_cash', 'difference', 'closed_at', 'user_id']) : null;

        $closure->fill([
            'user_id' => $request->user()->id,
            'business_date' => $selectedDate->toDateString(),
            'total_revenue' => $summary->total_revenue,
            'expected_cash' => $summary->expected_cash,
            'card_revenue' => $summary->card_revenue,
            'counted_cash' => $countedCash,
            'difference' => $countedCash - (float) $summary->expected_cash,
            'ticket_count' => $summary->ticket_count,
            'closed_at' => now(),
        ])->save();
        AuditLog::record($request, $activeBusiness, $wasExisting ? 'cash_closure.corrected' : 'cash_closure.created', $closure, $before, $closure->only(['counted_cash', 'difference', 'closed_at', 'user_id']));

        return redirect()->route('cash-closures.index', ['date' => $selectedDate->toDateString()])->with('status', 'Cierre de caja guardado correctamente.');
    }

    private function selectedDate(string $value): CarbonImmutable
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches) === 1 && checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            return CarbonImmutable::create((int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        return CarbonImmutable::now()->startOfDay();
    }

    private function dailySummary(Business $business, CarbonImmutable $date): Sale
    {
        return $business->sales()
            ->whereBetween('sold_at', [$date->startOfDay(), $date->endOfDay()])
            ->selectRaw(
                'COUNT(*) as ticket_count,
                COALESCE(SUM(total), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN total ELSE 0 END), 0) as expected_cash,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN total ELSE 0 END), 0) as card_revenue',
                [Sale::PAYMENT_CASH, Sale::PAYMENT_CARD],
            )->firstOrFail();
    }
}
