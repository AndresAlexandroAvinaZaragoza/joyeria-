<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\User;
use Carbon\CarbonImmutable;

class CustomerReportService
{
    public function generate(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $customerRoleIds = Rol::whereNotIn('nombre', ['admin', 'administrador', 'empleado'])->pluck('id_rol');
        $customers = User::with(['pedidos' => fn ($query) => $query->latest()])
            ->whereIn('rol_id', $customerRoleIds)
            ->when($from && $to, fn ($query) => $query->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->get();

        $items = $customers->map(function (User $customer): array {
            $orders = $customer->pedidos;
            $validOrders = $orders->where('estado', '<>', 'cancelado');

            return [
                'id' => $customer->getKey(),
                'name' => $this->customerName($customer),
                'email' => $customer->email,
                'orders' => $validOrders->count(),
                'total' => round((float) $validOrders->sum('total'), 2),
                'registered_at' => $customer->created_at,
                'last_order' => $orders->first()?->created_at,
            ];
        });

        return [
            'registered' => $items->count(),
            'frequent' => $items->where('orders', '>=', 2)->count(),
            'top_customers' => $items->sortByDesc('total')->take(10)->values()->all(),
            'history' => $items->sortByDesc('last_order')->values()->all(),
        ];
    }

    private function customerName(User $customer): string
    {
        $name = trim($customer->name);
        $lastNames = trim((string) $customer->apellidos);

        return $lastNames !== '' && ! str_ends_with(mb_strtolower($name), mb_strtolower($lastNames))
            ? $name.' '.$lastNames
            : $name;
    }
}
