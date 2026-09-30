<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Nuevo comercio
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('businesses.store') }}" class="flex flex-col gap-6 p-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nombre del comercio" />
                        <x-text-input
                            id="name"
                            name="name"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('name')"
                            required
                            autofocus
                            autocomplete="organization"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('businesses.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                            Cancelar
                        </a>
                        <x-primary-button>Guardar comercio</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
