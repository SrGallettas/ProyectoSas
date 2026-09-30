<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo producto</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $activeBusiness->name }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="flex flex-col gap-6 p-6" x-data="{ preview: null, updatePreview(event) { const file = event.target.files[0]; if (this.preview) URL.revokeObjectURL(this.preview); this.preview = file ? URL.createObjectURL(file) : null; } }">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nombre" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="image" value="Imagen del producto (opcional)" />
                        <div class="mt-1 flex items-center gap-4">
                            <img x-show="preview" :src="preview" alt="Vista previa de la imagen seleccionada" class="h-24 w-24 rounded-lg object-cover" />
                            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @change="updatePreview($event)" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG o WebP. Máximo 2 MB.</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price" value="Precio" />
                        <x-text-input id="price" name="price" type="number" class="mt-1 block w-full" :value="old('price')" required min="0" step="0.01" />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category_id" value="Categoría (opcional)" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Sin categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="stock" value="Stock (opcional)" />
                        <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" :value="old('stock')" min="0" step="1" />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('products.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">Cancelar</a>
                        <x-primary-button>Guardar producto</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
