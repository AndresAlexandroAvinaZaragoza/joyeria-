<?php

namespace App\Http\Controllers;

use App\Models\DireccionCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DireccionClienteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->direcciones()->count() >= 4) {
            return back()->withErrors(['direccion' => 'Solo puedes guardar hasta 4 direcciones.'], 'addresses');
        }

        $data = $this->validatedData($request);
        $data['user_id'] = $user->getKey();

        DB::transaction(function () use ($user, $data): void {
            $this->setDefaultAddress($user->getKey(), $data);
            DireccionCliente::create($data);
        });

        return back()->with('address_status', 'Dirección agregada correctamente.');
    }

    public function update(Request $request, int $direccion): RedirectResponse
    {
        $address = $request->user()->direcciones()->findOrFail($direccion);
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $address, $data): void {
            $this->setDefaultAddress($request->user()->getKey(), $data, $address->getKey());
            $address->update($data);
        });

        return back()->with('address_status', 'Dirección actualizada correctamente.');
    }

    public function destroy(Request $request, int $direccion): RedirectResponse
    {
        $address = $request->user()->direcciones()->findOrFail($direccion);
        $wasDefault = $address->predeterminada;
        $address->delete();

        if ($wasDefault) {
            $request->user()->direcciones()->latest('id_direccion_clientes')->first()?->update(['predeterminada' => true]);
        }

        return back()->with('address_status', 'Dirección eliminada correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validateWithBag('addresses', [
            'calle_numero' => ['required', 'string', 'max:50'],
            'colonia' => ['required', 'string', 'max:50'],
            'ciudad' => ['required', 'string', 'max:50'],
            'estado' => ['required', 'string', 'max:50'],
            'cp' => ['required', 'string', 'max:20'],
            'referencias' => ['nullable', 'string', 'max:100'],
            'predeterminada' => ['nullable', 'boolean'],
        ]);

        $data['predeterminada'] = (bool) ($data['predeterminada'] ?? false);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function setDefaultAddress(int $userId, array &$data, ?int $currentAddressId = null): void
    {
        if ($data['predeterminada']) {
            DireccionCliente::where('user_id', $userId)
                ->when($currentAddressId, fn ($query) => $query->where('id_direccion_clientes', '<>', $currentAddressId))
                ->update(['predeterminada' => false]);

            return;
        }

        $hasDefault = DireccionCliente::where('user_id', $userId)
            ->when($currentAddressId, fn ($query) => $query->where('id_direccion_clientes', '<>', $currentAddressId))
            ->where('predeterminada', true)
            ->exists();

        if (! $hasDefault) {
            $data['predeterminada'] = true;
        }
    }
}
