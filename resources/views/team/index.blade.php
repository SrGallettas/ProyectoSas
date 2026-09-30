<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-semibold">Equipo</h2><p class="text-sm text-gray-500">{{ $activeBusiness->name }}</p></div></x-slot>
    <div class="py-8"><div class="mx-auto grid max-w-5xl gap-6 px-4 lg:grid-cols-2">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold">Invitar a una persona</h3>
            <p class="mt-1 text-sm text-gray-500">El enlace caduca en siete días y solo funciona con el correo indicado.</p>
            @if (session('status'))<div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>@endif
            @if (session('invitation_url'))<div class="mt-3 rounded-lg bg-indigo-50 p-3 text-sm"><p class="font-semibold">Comparte este enlace:</p><input readonly value="{{ session('invitation_url') }}" class="mt-2 w-full rounded border-indigo-200 text-sm"></div>@endif
            <form method="POST" action="{{ route('team.invitations.store') }}" class="mt-5 space-y-4">@csrf
                <div><label for="email" class="text-sm font-medium">Correo</label><input id="email" name="email" type="email" required value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-gray-300"><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
                <div><label for="role" class="text-sm font-medium">Rol</label><select id="role" name="role" class="mt-1 w-full rounded-lg border-gray-300"><option value="staff">Camarero</option><option value="manager">Encargado</option></select></div>
                <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white">Crear invitación</button>
            </form>
        </section>
        <section class="rounded-2xl bg-white p-6 shadow-sm"><h3 class="text-lg font-bold">Personas con acceso</h3><div class="mt-4 divide-y">@foreach($members as $member)<div class="py-4"><div><p class="font-semibold">{{ $member->name }}</p><p class="text-sm text-gray-500">{{ $member->email }}</p></div>@if($member->pivot->role === 'owner')<span class="mt-2 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">Propietario</span>@else<form method="POST" action="{{ route('team.members.update', $member) }}" class="mt-3 flex flex-wrap items-center gap-2">@csrf @method('PATCH')<select name="role" class="rounded-lg border-gray-300 text-sm"><option value="staff" @selected($member->pivot->role === 'staff')>Camarero</option><option value="manager" @selected($member->pivot->role === 'manager')>Encargado</option></select><select name="is_active" class="rounded-lg border-gray-300 text-sm"><option value="1" @selected($member->pivot->is_active)>Activo</option><option value="0" @selected(!$member->pivot->is_active)>Desactivado</option></select><button class="rounded-lg bg-gray-800 px-3 py-2 text-sm font-semibold text-white">Guardar</button></form>@endif</div>@endforeach</div>
            @if($invitations->isNotEmpty())<h4 class="mt-6 font-semibold">Invitaciones pendientes</h4><div class="mt-2 divide-y">@foreach($invitations as $invitation)<div class="py-3 text-sm"><p>{{ $invitation->email }}</p><p class="text-gray-500">Caduca {{ $invitation->expires_at->format('d/m/Y H:i') }}</p><div class="mt-2 flex gap-2"><form method="POST" action="{{ route('team.invitations.regenerate', $invitation) }}">@csrf<button class="font-semibold text-indigo-600">Regenerar enlace</button></form><form method="POST" action="{{ route('team.invitations.destroy', $invitation) }}">@csrf @method('DELETE')<button class="font-semibold text-red-600">Cancelar</button></form></div></div>@endforeach</div>@endif
        </section>
    </div></div>
</x-app-layout>
