<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo cliente</h2><p class="mt-1 text-sm text-gray-500">{{ $activeBusiness->name }}</p></div></x-slot>
    <div class="py-12"><div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8"><div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('customers.store') }}" class="flex flex-col gap-6 p-6">
            @csrf
            <div><x-input-label for="name" value="Nombre" /><x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus /><x-input-error :messages="$errors->get('name')" class="mt-2" /></div>
            <div><x-input-label for="email" value="Correo electrónico (opcional)" /><x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
            <div><x-input-label for="phone" value="Teléfono (opcional)" /><x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" /><x-input-error :messages="$errors->get('phone')" class="mt-2" /></div>
            <div class="flex items-center justify-end gap-4"><a href="{{ route('customers.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">Cancelar</a><x-primary-button>Guardar cliente</x-primary-button></div>
        </form>
    </div></div></div>
</x-app-layout>
