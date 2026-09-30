<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold">Ticket #{{ $sale->id }}</h2>
            <a href="{{ route('sales.create') }}" class="rounded-lg bg-gray-800 px-5 py-3 text-sm font-semibold text-white">Nueva venta</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto flex max-w-xl flex-col gap-5 px-4">
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-5 text-center text-green-900" role="status">
                    <p class="text-lg font-bold">Venta completada</p>
                    <p class="mt-1 text-sm">{{ session('status') }}</p>
                </div>
            @endif

            <div class="bg-white p-8 shadow sm:rounded-lg">
                @if ($sale->voided_at)<div class="mb-5 rounded-xl bg-red-50 p-4 text-red-800"><p class="font-bold">Venta anulada</p><p class="text-sm">{{ $sale->void_reason }}</p></div>@endif
                <div class="border-b pb-4">
                    <h3 class="text-xl font-bold">{{ $activeBusiness->name }}</h3>
                    <p>{{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                    <p>{{ $sale->customer?->name ?? 'Venta sin identificar' }}</p>
                    <p class="text-sm text-gray-500">Atendido por: {{ $sale->user?->name ?? 'Sin registrar' }}</p>
                    <p class="mt-2 inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">Pago: {{ $sale->paymentMethodLabel() }}</p>
                </div>
                <div class="divide-y">
                    @foreach ($sale->lines as $line)
                        <div class="flex justify-between gap-4 py-3">
                            <div><p class="font-medium">{{ $line->product_name }}</p><p class="text-sm text-gray-500">{{ $line->quantity }} × {{ number_format((float) $line->unit_price, 2, ',', '.') }} €</p></div>
                            <p>{{ number_format((float) $line->line_total, 2, ',', '.') }} €</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between border-t pt-4 text-xl font-bold"><span>Total</span><span>{{ number_format((float) $sale->total, 2, ',', '.') }} €</span></div>
                @if($sale->refunds->isNotEmpty())<div class="mt-5 rounded-xl bg-amber-50 p-4"><p class="font-bold text-amber-900">Devoluciones</p>@foreach($sale->refunds as $refund)<div class="mt-2 flex justify-between text-sm"><span>{{ $refund->refunded_at->format('d/m/Y H:i') }} · {{ $refund->reason }}</span><strong>−{{ number_format((float)$refund->total,2,',','.') }} €</strong></div>@endforeach</div>@endif
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('sales.create') }}" class="rounded-lg bg-green-600 px-5 py-3 text-center font-bold text-white hover:bg-green-700">Empezar otra venta</a>
                    <a href="{{ route('sales.index') }}" class="rounded-lg bg-gray-100 px-5 py-3 text-center font-semibold text-gray-700 hover:bg-gray-200">Ver historial</a>
                </div>
                @php($canApprove=in_array(request()->attributes->get('activeBusinessRole'),['owner','manager'],true))
                @if (!$sale->voided_at)<form method="POST" action="{{ $canApprove ? route('sales.void', $sale) : route('adjustment-requests.store') }}" class="mt-6 border-t pt-5">@csrf @unless($canApprove)<input type="hidden" name="sale_id" value="{{ $sale->id }}"><input type="hidden" name="type" value="void">@endunless<label for="reason" class="text-sm font-medium">Motivo de anulación</label><div class="mt-2 flex gap-2"><input id="reason" name="reason" required minlength="5" maxlength="255" class="min-w-0 flex-1 rounded-lg border-gray-300" placeholder="Cobro duplicado, producto equivocado…"><button class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white">{{ $canApprove ? 'Anular venta' : 'Solicitar anulación' }}</button></div><x-input-error :messages="$errors->get('reason')" class="mt-2"/></form>@endif
                @if(!$sale->voided_at && in_array(request()->attributes->get('activeBusinessRole'), ['owner','manager'], true))<form method="POST" action="{{ route('sales.refunds.store',$sale) }}" class="mt-6 border-t pt-5">@csrf<h4 class="font-bold">Registrar devolución</h4><div class="mt-3 space-y-2">@foreach($sale->lines as $line)@php($available=$line->quantity-$line->refundLines->sum('quantity'))<div class="flex items-center justify-between gap-3"><label for="refund_{{ $line->id }}">{{ $line->product_name }} <span class="text-xs text-gray-500">({{ $available }} disponibles)</span></label><input id="refund_{{ $line->id }}" name="lines[{{ $line->id }}]" type="number" min="0" max="{{ $available }}" value="0" class="w-20 rounded-lg border-gray-300"></div>@endforeach</div><div class="mt-3 grid gap-3 sm:grid-cols-2"><select name="payment_method" class="rounded-lg border-gray-300"><option value="cash">Devolver en efectivo</option><option value="card">Devolver a tarjeta</option></select><input name="reason" required minlength="5" maxlength="255" class="rounded-lg border-gray-300" placeholder="Motivo de la devolución"></div><x-input-error :messages="$errors->all()" class="mt-2"/><button class="mt-3 rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white">Registrar devolución</button></form>@endif
                @if(!$sale->voided_at && !$canApprove)<form method="POST" action="{{ route('adjustment-requests.store') }}" class="mt-6 border-t pt-5">@csrf<input type="hidden" name="sale_id" value="{{ $sale->id }}"><input type="hidden" name="type" value="refund"><h4 class="font-bold">Solicitar devolución</h4><div class="mt-3 space-y-2">@foreach($sale->lines as $line)@php($available=$line->quantity-$line->refundLines->sum('quantity'))<div class="flex items-center justify-between gap-3"><label>{{ $line->product_name }} ({{ $available }} disponibles)</label><input name="lines[{{ $line->id }}]" type="number" min="0" max="{{ $available }}" value="0" class="w-20 rounded-lg border-gray-300"></div>@endforeach</div><div class="mt-3 grid gap-3 sm:grid-cols-2"><select name="payment_method" class="rounded-lg border-gray-300"><option value="cash">Efectivo</option><option value="card">Tarjeta</option></select><input name="reason" required minlength="5" maxlength="255" class="rounded-lg border-gray-300" placeholder="Motivo"></div><button class="mt-3 rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white">Enviar solicitud</button></form>@endif
            </div>
        </div>
    </div>
</x-app-layout>
