<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Sale;
use App\Models\SaleLine;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $requestedPeriod = $request->string('period')->toString();
        $period = in_array($requestedPeriod, ['day', 'week', 'month'], true) ? $requestedPeriod : 'day';
        $selectedDate = $this->resolveSelectedDate($request, $period);
        $rangeStart = match ($period) {
            'week' => $selectedDate->startOfWeek(),
            'month' => $selectedDate->startOfMonth(),
            default => $selectedDate->startOfDay(),
        };
        $rangeEnd = match ($period) {
            'week' => $selectedDate->endOfWeek(),
            'month' => $selectedDate->endOfMonth(),
            default => $selectedDate->endOfDay(),
        };
        $selectedRange = [$rangeStart, $rangeEnd];

        $summary = $activeBusiness->sales()
            ->completed()
            ->whereBetween('sold_at', $selectedRange)
            ->selectRaw(
                'COUNT(*) as ticket_count,
                COALESCE(SUM(total), 0) as revenue,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN total ELSE 0 END), 0) as cash_revenue,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN total ELSE 0 END), 0) as card_revenue,
                SUM(CASE WHEN payment_method = ? THEN 1 ELSE 0 END) as cash_ticket_count,
                SUM(CASE WHEN payment_method = ? THEN 1 ELSE 0 END) as card_ticket_count',
                [Sale::PAYMENT_CASH, Sale::PAYMENT_CARD, Sale::PAYMENT_CASH, Sale::PAYMENT_CARD],
            )
            ->firstOrFail();

        $ticketCount = (int) $summary->ticket_count;
        $revenue = (float) $summary->revenue;
        $averageTicket = $ticketCount > 0 ? $revenue / $ticketCount : 0.0;
        $previousRangeStart = match ($period) {
            'week' => $rangeStart->subWeek(),
            'month' => $rangeStart->subMonthNoOverflow(),
            default => $rangeStart->subDay(),
        };
        $previousRangeEnd = match ($period) {
            'week' => $rangeEnd->subWeek(),
            'month' => $rangeEnd->subMonthNoOverflow()->endOfMonth(),
            default => $rangeEnd->subDay(),
        };
        $previousSummary = $activeBusiness->sales()
            ->completed()
            ->whereBetween('sold_at', [$previousRangeStart, $previousRangeEnd])
            ->selectRaw('COUNT(*) as ticket_count, COALESCE(SUM(total), 0) as revenue')
            ->firstOrFail();
        $revenueChange = $this->percentageChange((float) $previousSummary->revenue, $revenue);
        $ticketChange = $this->percentageChange((float) $previousSummary->ticket_count, $ticketCount);
        $recentSales = $activeBusiness->sales()
            ->completed()
            ->with('customer')
            ->whereBetween('sold_at', $selectedRange)
            ->latest('sold_at')
            ->latest('id')
            ->limit(10)
            ->get();
        $topProducts = SaleLine::query()
            ->join('sales', 'sales.id', '=', 'sale_lines.sale_id')
            ->where('sales.business_id', $activeBusiness->id)
            ->whereNull('sales.voided_at')
            ->whereBetween('sales.sold_at', $selectedRange)
            ->select('sale_lines.product_name')
            ->selectRaw('SUM(sale_lines.quantity) as units_sold, SUM(sale_lines.line_total) as revenue')
            ->groupBy('sale_lines.product_name')
            ->orderByDesc('units_sold')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
        $lowStockProducts = $activeBusiness->products()
            ->whereNotNull('stock')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(8)
            ->get();
        $dateFormat = $period === 'month' ? 'Y-m' : 'Y-m-d';
        $previousDate = match ($period) {
            'week' => $selectedDate->subWeek()->format($dateFormat),
            'month' => $selectedDate->subMonthNoOverflow()->format($dateFormat),
            default => $selectedDate->subDay()->format($dateFormat),
        };
        $nextDate = match ($period) {
            'week' => $selectedDate->addWeek()->format($dateFormat),
            'month' => $selectedDate->addMonthNoOverflow()->format($dateFormat),
            default => $selectedDate->addDay()->format($dateFormat),
        };
        $periodLabel = match ($period) {
            'week' => 'Semana del '.$rangeStart->locale('es')->translatedFormat('j \d\e F').' al '.$rangeEnd->locale('es')->translatedFormat('j \d\e F \d\e Y'),
            'month' => $selectedDate->locale('es')->translatedFormat('F \d\e Y'),
            default => $selectedDate->locale('es')->translatedFormat('l, j \d\e F \d\e Y'),
        };
        $today = CarbonImmutable::now();
        $quickPeriods = [
            'today' => $today->format('Y-m-d'),
            'yesterday' => $today->subDay()->format('Y-m-d'),
            'previousWeek' => $today->subWeek()->format('Y-m-d'),
            'currentMonth' => $today->format('Y-m'),
            'previousMonth' => $today->subMonthNoOverflow()->format('Y-m'),
        ];

        return view('dashboard', compact(
            'activeBusiness',
            'averageTicket',
            'dateFormat',
            'lowStockProducts',
            'nextDate',
            'period',
            'periodLabel',
            'previousDate',
            'quickPeriods',
            'recentSales',
            'revenue',
            'revenueChange',
            'selectedDate',
            'summary',
            'ticketCount',
            'ticketChange',
            'topProducts',
        ));
    }

    private function resolveSelectedDate(Request $request, string $period): CarbonImmutable
    {
        $value = $request->string('date')->toString();
        $pattern = $period === 'month' ? '/^(\d{4})-(\d{2})$/' : '/^(\d{4})-(\d{2})-(\d{2})$/';

        if (preg_match($pattern, $value, $matches) === 1) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = $period === 'month' ? 1 : (int) $matches[3];

            if (checkdate($month, $day, $year)) {
                return CarbonImmutable::create($year, $month, $day, 0, 0, 0, config('app.timezone'));
            }
        }

        return CarbonImmutable::now()->startOfDay();
    }

    private function percentageChange(float $previous, float $current): ?float
    {
        if ($previous === 0.0) {
            return $current === 0.0 ? 0.0 : null;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
