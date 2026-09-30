<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Panel del local</h2>
                <p class="text-sm text-gray-500">{{ $activeBusiness->name }} · {{ ucfirst($periodLabel) }}</p>
            </div>
            <a href="{{ route('sales.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 active:scale-95">Nueva venta</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            <section class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm" aria-label="Seleccionar periodo">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="inline-flex w-fit rounded-xl bg-gray-100 p-1">
                        <a href="{{ route('dashboard', ['period' => 'day', 'date' => $selectedDate->format('Y-m-d')]) }}" class="rounded-lg px-5 py-2 text-sm font-semibold {{ $period === 'day' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Día</a>
                        <a href="{{ route('dashboard', ['period' => 'week', 'date' => $selectedDate->format('Y-m-d')]) }}" class="rounded-lg px-5 py-2 text-sm font-semibold {{ $period === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Semana</a>
                        <a href="{{ route('dashboard', ['period' => 'month', 'date' => $selectedDate->format('Y-m')]) }}" class="rounded-lg px-5 py-2 text-sm font-semibold {{ $period === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}">Mes</a>
                    </div>
                    <nav class="flex items-center gap-2" aria-label="Navegar entre periodos">
                        <a href="{{ route('dashboard', ['period' => $period, 'date' => $previousDate]) }}" class="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-300 bg-white text-xl font-bold text-gray-700 hover:bg-gray-50" aria-label="Periodo anterior">←</a>
                        <form method="GET" action="{{ route('dashboard') }}" class="flex-1">
                            <input type="hidden" name="period" value="{{ $period }}">
                            <label for="dashboard_date" class="sr-only">Seleccionar {{ $period === 'month' ? 'mes' : 'día' }}</label>
                            <input id="dashboard_date" name="date" type="{{ $period === 'month' ? 'month' : 'date' }}" value="{{ $selectedDate->format($dateFormat) }}" onchange="this.form.submit()" class="h-11 w-full rounded-xl border-gray-300 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </form>
                        <a href="{{ route('dashboard', ['period' => $period, 'date' => $nextDate]) }}" class="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-300 bg-white text-xl font-bold text-gray-700 hover:bg-gray-50" aria-label="Periodo siguiente">→</a>
                    </nav>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <a href="{{ route('dashboard', ['period' => 'day', 'date' => $quickPeriods['today']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Hoy</a>
                    <a href="{{ route('dashboard', ['period' => 'day', 'date' => $quickPeriods['yesterday']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Ayer</a>
                    <a href="{{ route('dashboard', ['period' => 'week', 'date' => $quickPeriods['today']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Esta semana</a>
                    <a href="{{ route('dashboard', ['period' => 'week', 'date' => $quickPeriods['previousWeek']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Semana pasada</a>
                    <a href="{{ route('dashboard', ['period' => 'month', 'date' => $quickPeriods['currentMonth']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Este mes</a>
                    <a href="{{ route('dashboard', ['period' => 'month', 'date' => $quickPeriods['previousMonth']]) }}" class="shrink-0 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Mes pasado</a>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Resumen del periodo">
                <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 text-white shadow-sm">
                    <p class="text-sm font-medium text-indigo-100">Facturación del periodo</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight">{{ number_format($revenue, 2, ',', '.') }} €</p>
                    <p class="mt-3 text-sm text-indigo-100">{{ $revenueChange === null ? 'Sin facturación en el periodo anterior' : sprintf('%+.1f %% frente al periodo anterior', $revenueChange) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-gray-500">Tickets del periodo</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-gray-900">{{ $ticketCount }}</p>
                    <p class="mt-3 text-sm text-gray-500">{{ $ticketChange === null ? 'Sin tickets en el periodo anterior' : sprintf('%+.1f %% frente al periodo anterior', $ticketChange) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:col-span-2 xl:col-span-1">
                    <p class="text-sm font-medium text-gray-500">Ticket medio</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight text-gray-900">{{ number_format($averageTicket, 2, ',', '.') }} €</p>
                    <p class="mt-3 text-sm text-gray-500">Importe medio de cada venta del periodo</p>
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-2" aria-label="Desglose por método de pago">
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-green-200 bg-green-50 p-5">
                    <div><p class="font-semibold text-green-950">Efectivo</p><p class="text-sm text-green-700">{{ (int) $summary->cash_ticket_count }} {{ (int) $summary->cash_ticket_count === 1 ? 'ticket' : 'tickets' }}</p></div>
                    <p class="text-2xl font-bold text-green-800">{{ number_format((float) $summary->cash_revenue, 2, ',', '.') }} €</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
                    <div><p class="font-semibold text-indigo-950">Tarjeta</p><p class="text-sm text-indigo-700">{{ (int) $summary->card_ticket_count }} {{ (int) $summary->card_ticket_count === 1 ? 'ticket' : 'tickets' }}</p></div>
                    <p class="text-2xl font-bold text-indigo-800">{{ number_format((float) $summary->card_revenue, 2, ',', '.') }} €</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4 sm:px-6">
                    <div><h3 class="font-bold text-gray-900">Ventas recientes</h3><p class="text-sm text-gray-500">Los últimos movimientos del periodo</p></div>
                    <a href="{{ route('sales.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Ver historial</a>
                </div>

                @if ($recentSales->isEmpty())
                    <div class="flex flex-col items-center gap-4 px-6 py-12 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-2xl" aria-hidden="true">☕</div>
                        <div><p class="font-semibold text-gray-900">No hay ventas en este periodo</p><p class="text-sm text-gray-500">Prueba con otro día o mes para consultar su actividad.</p></div>
                        <a href="{{ route('sales.create') }}" class="rounded-lg bg-green-600 px-5 py-3 text-sm font-bold text-white hover:bg-green-700">Registrar nueva venta</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Hora</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Cliente</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Pago</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6">Total</th><th class="px-5 py-3 sm:px-6"><span class="sr-only">Acciones</span></th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentSales as $sale)
                                    <tr class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-900 sm:px-6">{{ $sale->sold_at->format('H:i') }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-gray-600 sm:px-6">{{ $sale->customer?->name ?? 'Sin identificar' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 sm:px-6"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $sale->payment_method === \App\Models\Sale::PAYMENT_CARD ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700' }}">{{ $sale->paymentMethodLabel() }}</span></td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-gray-900 sm:px-6">{{ number_format((float) $sale->total, 2, ',', '.') }} €</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6"><a href="{{ route('sales.show', $sale) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Ver ticket</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                        <h3 class="font-bold text-gray-900">Productos más vendidos</h3>
                        <p class="text-sm text-gray-500">Clasificación del periodo seleccionado</p>
                    </div>
                    @if ($topProducts->isEmpty())
                        <p class="px-6 py-10 text-center text-sm text-gray-500">No hay productos vendidos en este periodo.</p>
                    @else
                        <ol class="divide-y divide-gray-100">
                            @foreach ($topProducts as $topProduct)
                                <li class="flex items-center gap-4 px-5 py-4 sm:px-6">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ $loop->iteration }}</span>
                                    <div class="min-w-0 flex-1"><p class="truncate font-semibold text-gray-900">{{ $topProduct->product_name }}</p><p class="text-sm text-gray-500">{{ (int) $topProduct->units_sold }} unidades</p></div>
                                    <p class="whitespace-nowrap font-bold text-gray-900">{{ number_format((float) $topProduct->revenue, 2, ',', '.') }} €</p>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>

                <section class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-amber-200 bg-amber-50 px-5 py-4 sm:px-6">
                        <div><h3 class="font-bold text-amber-950">Stock bajo</h3><p class="text-sm text-amber-700">Productos con 5 unidades o menos</p></div>
                        <span class="rounded-full bg-amber-200 px-3 py-1 text-sm font-bold text-amber-900">{{ $lowStockProducts->count() }}</span>
                    </div>
                    @if ($lowStockProducts->isEmpty())
                        <div class="px-6 py-10 text-center"><p class="font-semibold text-green-700">Stock bajo control</p><p class="text-sm text-gray-500">No hay productos que necesiten reposición.</p></div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($lowStockProducts as $product)
                                <li class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                                    <div class="min-w-0"><p class="truncate font-semibold text-gray-900">{{ $product->name }}</p><p class="text-sm font-medium {{ $product->stock === 0 ? 'text-red-600' : 'text-amber-700' }}">{{ $product->stock === 0 ? 'Agotado' : $product->stock.' unidades disponibles' }}</p></div>
                                    <a href="{{ route('products.edit', $product) }}" class="shrink-0 rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">Editar</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
