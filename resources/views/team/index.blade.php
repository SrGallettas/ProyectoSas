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
        <section class="rounded-2xl bg-white p-6 shadow-sm"><h3 class="text-lg font-bold">Personas con acceso</h3><div class="mt-4 divide-y">@foreach($members as $member)<div class="flex justify-between py-3"><div><p class="font-semibold">{{ $member->name }}</p><p class="text-sm text-gray-500">{{ $member->email }}</p></div><span class="h-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold">{{ ['owner' => 'Propietario', 'manager' => 'Encargado', 'staff' => 'Camarero'][$member->pivot->role] }}</span></div>@endforeach</div>
            @if($invitations->isNotEmpty())<h4 class="mt-6 font-semibold">Invitaciones pendientes</h4><div class="mt-2 divide-y">@foreach($invitations as $invitation)<div class="py-3 text-sm"><p>{{ $invitation->email }}</p><p class="text-gray-500">Caduca {{ $invitation->expires_at->format('d/m/Y H:i') }}</p></div>@endforeach</div>@endif
        </section>
    </div></div>
</x-app-layout>
