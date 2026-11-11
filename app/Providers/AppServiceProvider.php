<?php

namespace App\Providers;

use App\Models\Envio;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navigation', function (\Illuminate\View\View $view): void {
            $user = Auth::user();
            $role = strtolower((string) $user?->rol?->nombre);

            if (! $user || ! in_array($role, ['admin', 'administrador', 'empleado'], true)) {
                $view->with([
                    'newOrdersCount' => 0,
                    'pendingShipmentsCount' => 0,
                ]);

                return;
            }

            $view->with([
                'newOrdersCount' => Pedido::query()
                    ->where('estado', 'pendiente')
                    ->whereHas('pago', fn ($query) => $query->where('estado', 'completado'))
                    ->count(),
                'pendingShipmentsCount' => Envio::query()
                    ->whereHas('pedido', fn ($query) => $query->whereNotIn('estado', ['cancelado', 'entregado']))
                    ->where(function ($query): void {
                        $query->where('estado', 'pendiente')
                            ->orWhereNull('paqueteria')
                            ->orWhereNull('numero_guia')
                            ->orWhere('paqueteria', '')
                            ->orWhere('numero_guia', '');
                    })
                    ->count(),
            ]);
        });
    }
}
