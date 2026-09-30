<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Nueva venta</h2>
                <p class="text-sm text-gray-500">{{ $activeBusiness->name }}</p>
            </div>
            <a href="{{ route('sales.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Ver historial</a>
        </div>
    </x-slot>

    <div
        class="py-6"
        x-data="{
            category: 'all',
            cart: [],
            submitting: false,
            paymentMethod: null,
            add(product) {
                const existing = this.cart.find(item => item.id === product.id);
                if (existing) {
                    if (existing.stock === null || existing.quantity < existing.stock) existing.quantity++;
                } else if (product.stock === null || product.stock > 0) {
                    this.cart.push({ ...product, quantity: 1 });
                }
            },
            increase(item) {
                if (item.stock === null || item.quantity < item.stock) item.quantity++;
            },
            decrease(item) {
                item.quantity--;
                if (item.quantity <= 0) this.remove(item.id);
            },
            remove(id) { this.cart = this.cart.filter(item => item.id !== id); },
            total() { return this.cart.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0); },
            money(value) { return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' €'; }
        }"
    >
        <form method="POST" action="{{ route('sales.store') }}" @submit="if (submitting) { $event.preventDefault(); return; } submitting = true" class="mx-auto grid max-w-[1600px] gap-6 px-4 lg:grid-cols-[minmax(0,1fr)_380px] lg:px-6">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
            <input type="hidden" name="payment_method" :value="paymentMethod">

            <section class="min-w-0">
                <div class="mb-5 flex gap-2 overflow-x-auto pb-2">
                    <button type="button" @click="category = 'all'" :class="category === 'all' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" class="shrink-0 rounded-full px-5 py-3 text-sm font-semibold shadow-sm">Todos</button>
                    @foreach ($categories as $category)
                        <button type="button" @click="category = '{{ $category->id }}'" :class="category === '{{ $category->id }}' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" class="shrink-0 rounded-full px-5 py-3 text-sm font-semibold shadow-sm">
                            {{ $category->name }}
                        </button>
                    @endforeach
                    @if ($uncategorizedProducts->isNotEmpty())
                        <button type="button" @click="category = 'none'" :class="category === 'none' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700'" class="shrink-0 rounded-full px-5 py-3 text-sm font-semibold shadow-sm">Sin categoría</button>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    @foreach ($categories as $category)
                        @foreach ($category->products as $product)
                            <button
                                x-show="category === 'all' || category === '{{ $category->id }}'"
                                type="button"
                                @click="add({ id: {{ $product->id }}, name: {{ Js::from($product->name) }}, price: {{ Js::from($product->price) }}, stock: {{ Js::from($product->stock) }} })"
                                @disabled($product->stock === 0)
                                class="flex min-h-44 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white text-left shadow-sm transition hover:border-indigo-400 hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:opacity-60"
                            >
                                <img src="{{ $product->imageUrl() }}" alt="" class="h-24 w-full object-cover">
                                <span class="flex flex-1 flex-col justify-between gap-2 p-3"><span class="font-semibold text-gray-900">{{ $product->name }}</span><span class="flex items-end justify-between gap-2">
                                    <span class="text-lg font-bold text-indigo-700">{{ number_format((float) $product->price, 2, ',', '.') }} €</span>
                                    @if ($product->stock !== null)<span class="text-xs text-gray-400">{{ $product->stock }} ud.</span>@endif
                                </span></span>
                            </button>
                        @endforeach
                    @endforeach
                    @foreach ($uncategorizedProducts as $product)
                        <button x-show="category === 'all' || category === 'none'" type="button" @click="add({ id: {{ $product->id }}, name: {{ Js::from($product->name) }}, price: {{ Js::from($product->price) }}, stock: {{ Js::from($product->stock) }} })" @disabled($product->stock === 0) class="flex min-h-44 flex-col overflow-hidden rounded-xl border bg-white text-left shadow-sm active:scale-95 disabled:opacity-60">
                            <img src="{{ $product->imageUrl() }}" alt="" class="h-24 w-full object-cover"><span class="flex flex-1 flex-col justify-between gap-2 p-3"><span class="font-semibold">{{ $product->name }}</span><span class="text-lg font-bold text-indigo-700">{{ number_format((float) $product->price, 2, ',', '.') }} €</span></span>
                        </button>
                    @endforeach
                </div>
            </section>

            <aside class="flex h-fit max-h-[calc(100vh-8rem)] flex-col rounded-2xl bg-white shadow-lg lg:sticky lg:top-4">
                <div class="border-b p-5">
                    <h3 class="text-lg font-bold text-gray-900">Ticket actual</h3>
                    <label for="customer_id" class="mt-4 block text-sm font-medium text-gray-700">Cliente opcional</label>
                    <select id="customer_id" name="customer_id" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Venta sin identificar</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex max-h-[45vh] min-h-48 flex-col gap-3 overflow-y-auto p-5">
                    <p x-show="cart.length === 0" class="m-auto text-center text-sm text-gray-400">Toca un producto para añadirlo.</p>
                    <template x-for="item in cart" :key="item.id">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <input type="hidden" :name="`products[${item.id}]`" :value="item.quantity">
                            <div class="flex justify-between gap-3"><span class="font-medium text-gray-900" x-text="item.name"></span><button type="button" @click="remove(item.id)" class="text-xl leading-none text-red-500" aria-label="Eliminar">×</button></div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2"><button type="button" @click="decrease(item)" class="h-10 w-10 rounded-lg bg-gray-100 text-xl font-bold">−</button><span class="w-8 text-center font-bold" x-text="item.quantity"></span><button type="button" @click="increase(item)" class="h-10 w-10 rounded-lg bg-gray-900 text-xl font-bold text-white">+</button></div>
                                <span class="font-semibold" x-text="money(Number(item.price) * item.quantity)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-t p-5">
                    <x-input-error :messages="$errors->all()" class="mb-4" />
                    <div class="mb-4 flex items-center justify-between text-2xl font-bold"><span>Total</span><span x-text="money(total())">0,00 €</span></div>
                    <fieldset :disabled="cart.length === 0 || submitting" class="grid grid-cols-2 gap-3">
                        <legend class="sr-only">Método de pago</legend>
                        <button type="submit" @click="paymentMethod = 'cash'" class="rounded-xl bg-green-600 px-4 py-4 text-base font-bold text-white shadow transition hover:bg-green-700 active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-300">
                            <span x-show="!submitting">Efectivo</span>
                            <span x-show="submitting">Cobrando…</span>
                        </button>
                        <button type="submit" @click="paymentMethod = 'card'" class="rounded-xl bg-indigo-600 px-4 py-4 text-base font-bold text-white shadow transition hover:bg-indigo-700 active:scale-95 disabled:cursor-not-allowed disabled:bg-gray-300">
                            <span x-show="!submitting">Tarjeta</span>
                            <span x-show="submitting">Cobrando…</span>
                        </button>
                    </fieldset>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
