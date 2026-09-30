<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Mis comercios
            </h2>

            <a href="{{ route('businesses.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">
                Nuevo comercio
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6">
                @if (session('status'))
                    <div class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @forelse ($businesses as $business)
                            <div class="flex flex-col justify-between gap-4 border-b border-gray-200 py-4 first:pt-0 last:border-b-0 last:pb-0 sm:flex-row sm:items-center">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-semibold text-gray-900">{{ $business->name }}</h3>
                                        @if ($activeBusinessId === $business->id)
                                            <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                Activo
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Creado el {{ $business->created_at->format('d/m/Y') }}
                                    </p>
                                </div>

                                @if ($activeBusinessId !== $business->id)
                                    <form method="POST" action="{{ route('businesses.select', $business) }}">
                                        @csrf
                                        <x-secondary-button type="submit">Trabajar aquí</x-secondary-button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="text-center">
                                <h3 class="font-semibold text-gray-900">Aún no tienes comercios</h3>
                                <p class="mt-1 text-sm text-gray-500">Crea el primero para empezar a gestionar sus productos y clientes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
