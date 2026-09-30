<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clientes</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $activeBusiness->name }}</p>
            </div>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900">Nuevo cliente</a>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                @if ($customers->isEmpty())
                    <div class="p-6 text-center">
                        <h3 class="font-semibold text-gray-900">Aún no hay clientes</h3>
                        <p class="mt-1 text-sm text-gray-500">Crea el primer cliente de este comercio.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Correo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Teléfono</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($customers as $customer)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ $customer->name }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ $customer->email ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ $customer->phone ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-6 py-4"><div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('customers.edit', $customer) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('¿Seguro que quieres eliminar este cliente?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        </div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            {{ $customers->links() }}
        </div>
    </div>
</x-app-layout>
