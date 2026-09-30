<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-semibold text-gray-800">Categorías</h2><p class="mt-1 text-sm text-gray-500">{{ $activeBusiness->name }}</p></div><a href="{{ route('categories.create') }}" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase text-white">Nueva categoría</a></div></x-slot>
    <div class="py-12"><div class="mx-auto flex max-w-4xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
        @if (session('status'))<div class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>@endif
        <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
            @forelse ($categories as $category)
                <div class="flex items-center justify-between gap-4 border-b border-gray-200 py-4 first:pt-0 last:border-0 last:pb-0">
                    <div><p class="font-semibold text-gray-900">{{ $category->name }}</p><p class="text-sm text-gray-500">{{ $category->products_count }} productos</p></div>
                    <div class="flex gap-3"><a href="{{ route('categories.edit', $category) }}" class="text-sm font-medium text-indigo-600">Editar</a><form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría? Los productos quedarán sin categoría.')">@csrf @method('DELETE')<button class="text-sm font-medium text-red-600">Eliminar</button></form></div>
                </div>
            @empty
                <p class="text-center text-gray-500">Aún no hay categorías.</p>
            @endforelse
        </div>
    </div></div>
</x-app-layout>
