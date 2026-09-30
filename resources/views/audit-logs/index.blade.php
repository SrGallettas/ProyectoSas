<x-app-layout>
    <x-slot name="header">
        <div><h2 class="text-xl font-semibold">Registro de actividad</h2><p class="text-sm text-gray-500">{{ $activeBusiness->name }}</p></div>
    </x-slot>
    <div class="py-8"><div class="mx-auto max-w-6xl px-4"><div class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full divide-y"><thead class="bg-gray-50"><tr><th class="p-4 text-left">Fecha</th><th class="p-4 text-left">Usuario</th><th class="p-4 text-left">Acción</th><th class="p-4 text-left">Detalles</th><th class="p-4 text-left">IP</th></tr></thead>
            <tbody class="divide-y">@forelse($logs as $log)<tr class="align-top"><td class="whitespace-nowrap p-4 text-sm">{{ $log->created_at->format('d/m/Y H:i:s') }}</td><td class="p-4">{{ $log->user?->name ?? 'Usuario eliminado' }}</td><td class="p-4 font-semibold">{{ $log->actionLabel() }}</td><td class="p-4 text-sm text-gray-600"><ul class="space-y-1">@foreach($log->changeSummary() as $detail)<li>{{ $detail }}</li>@endforeach</ul></td><td class="p-4 text-sm text-gray-500">{{ $log->ip_address ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500">Todavía no hay operaciones sensibles registradas.</td></tr>@endforelse</tbody>
        </table></div><div class="p-4">{{ $logs->links() }}</div>
    </div></div></div>
</x-app-layout>
