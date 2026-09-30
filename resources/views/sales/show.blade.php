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
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('sales.create') }}" class="rounded-lg bg-green-600 px-5 py-3 text-center font-bold text-white hover:bg-green-700">Empezar otra venta</a>
                    <a href="{{ route('sales.index') }}" class="rounded-lg bg-gray-100 px-5 py-3 text-center font-semibold text-gray-700 hover:bg-gray-200">Ver historial</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
