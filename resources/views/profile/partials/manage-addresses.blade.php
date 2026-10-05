<section>
    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-medium text-gray-900">Mis direcciones</h2>
            <p class="mt-1 text-sm text-gray-600">Guarda hasta 4 direcciones para tus pedidos.</p>
        </div>
        <span class="shrink-0 rounded-md bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ $direcciones->count() }}/4</span>
    </header>

    @if (session('address_status'))
        <p class="mt-4 text-sm text-green-600">{{ session('address_status') }}</p>
    @endif

    @if ($errors->getBag('addresses')->has('direccion'))
        <p class="mt-4 text-sm text-red-600">{{ $errors->getBag('addresses')->first('direccion') }}</p>
    @endif

    <div class="mt-6 space-y-4">
        @forelse ($direcciones as $direccion)
            <form method="POST" action="{{ route('profile.addresses.update', $direccion) }}" class="rounded-lg border border-gray-200 p-4">
                @csrf
                @method('PUT')
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="font-medium text-gray-900">Dirección {{ $loop->iteration }}</h3>
                    @if ($direccion->predeterminada)
                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">Predeterminada</span>
                    @endif
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="calle_numero_{{ $direccion->getKey() }}" :value="__('Calle y número')" />
                        <x-text-input id="calle_numero_{{ $direccion->getKey() }}" name="calle_numero" type="text" class="mt-1 block w-full" :value="old('calle_numero', $direccion->calle_numero)" required />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('calle_numero')" />
                    </div>
                    <div>
                        <x-input-label for="colonia_{{ $direccion->getKey() }}" :value="__('Colonia')" />
                        <x-text-input id="colonia_{{ $direccion->getKey() }}" name="colonia" type="text" class="mt-1 block w-full" :value="old('colonia', $direccion->colonia)" required />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('colonia')" />
                    </div>
                    <div>
                        <x-input-label for="ciudad_{{ $direccion->getKey() }}" :value="__('Ciudad')" />
                        <x-text-input id="ciudad_{{ $direccion->getKey() }}" name="ciudad" type="text" class="mt-1 block w-full" :value="old('ciudad', $direccion->ciudad)" required />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('ciudad')" />
                    </div>
                    <div>
                        <x-input-label for="estado_{{ $direccion->getKey() }}" :value="__('Estado')" />
                        <x-text-input id="estado_{{ $direccion->getKey() }}" name="estado" type="text" class="mt-1 block w-full" :value="old('estado', $direccion->estado)" required />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('estado')" />
                    </div>
                    <div>
                        <x-input-label for="cp_{{ $direccion->getKey() }}" :value="__('Código postal')" />
                        <x-text-input id="cp_{{ $direccion->getKey() }}" name="cp" type="text" class="mt-1 block w-full" :value="old('cp', $direccion->cp)" required />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('cp')" />
                    </div>
                    <div>
                        <x-input-label for="referencias_{{ $direccion->getKey() }}" :value="__('Referencias')" />
                        <x-text-input id="referencias_{{ $direccion->getKey() }}" name="referencias" type="text" class="mt-1 block w-full" :value="old('referencias', $direccion->referencias)" />
                        <x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('referencias')" />
                    </div>
                </div>
                <label class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="predeterminada" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($direccion->predeterminada)>
                    Usar como dirección predeterminada
                </label>
                <div class="mt-4 flex items-center justify-between gap-4">
                    <button type="submit" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700">Guardar cambios</button>
                    <button type="submit" form="delete-address-{{ $direccion->getKey() }}" class="text-sm text-red-600 underline hover:text-red-800">Eliminar</button>
                </div>
            </form>
            <form id="delete-address-{{ $direccion->getKey() }}" method="POST" action="{{ route('profile.addresses.destroy', $direccion) }}" onsubmit="return confirm('¿Deseas eliminar esta dirección?');">
                @csrf
                @method('DELETE')
            </form>
        @empty
            <p class="rounded-lg border border-dashed border-gray-300 p-5 text-sm text-gray-600">Todavía no tienes direcciones guardadas.</p>
        @endforelse
    </div>

    @if ($direcciones->count() < 4)
        <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="font-medium text-gray-900">Agregar dirección</h3>
            <form method="POST" action="{{ route('profile.addresses.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><x-input-label for="new_calle_numero" :value="__('Calle y número')" /><x-text-input id="new_calle_numero" name="calle_numero" type="text" class="mt-1 block w-full" :value="old('calle_numero')" required /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('calle_numero')" /></div>
                    <div><x-input-label for="new_colonia" :value="__('Colonia')" /><x-text-input id="new_colonia" name="colonia" type="text" class="mt-1 block w-full" :value="old('colonia')" required /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('colonia')" /></div>
                    <div><x-input-label for="new_ciudad" :value="__('Ciudad')" /><x-text-input id="new_ciudad" name="ciudad" type="text" class="mt-1 block w-full" :value="old('ciudad')" required /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('ciudad')" /></div>
                    <div><x-input-label for="new_estado" :value="__('Estado')" /><x-text-input id="new_estado" name="estado" type="text" class="mt-1 block w-full" :value="old('estado')" required /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('estado')" /></div>
                    <div><x-input-label for="new_cp" :value="__('Código postal')" /><x-text-input id="new_cp" name="cp" type="text" class="mt-1 block w-full" :value="old('cp')" required /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('cp')" /></div>
                    <div><x-input-label for="new_referencias" :value="__('Referencias')" /><x-text-input id="new_referencias" name="referencias" type="text" class="mt-1 block w-full" :value="old('referencias')" /><x-input-error class="mt-2" :messages="$errors->getBag('addresses')->get('referencias')" /></div>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="predeterminada" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($direcciones->isEmpty())> Usar como dirección predeterminada</label>
                <x-primary-button>Agregar dirección</x-primary-button>
            </form>
        </div>
    @endif
</section>
