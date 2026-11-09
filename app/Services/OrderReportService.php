<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class OrderReportService
{
    public function generate(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $orders = Pedido::with('usuario')
            ->when($from && $to, fn ($query) => $query->whereBetween('created_at', [$from, $to]))
            ->latest()
            ->get();
        $customers = $orders->groupBy('user_id');

        return [
            'total' => $orders->count(),
            'pending' => $orders->whereNotIn('estado', ['cancelado', 'entregado'])->count(),
            'completed' => $orders->where('estado', 'entregado')->count(),
            'cancelled' => $orders->where('estado', 'cancelado')->count(),
            'customers' => $customers
                ->map(fn (Collection $customerOrders): array => [
                    'name' => $this->customerName($customerOrders->first()->usuario),
                    'email' => $customerOrders->first()->usuario?->email ?? '—',
                    'orders' => $customerOrders->count(),
                    'total' => round((float) $customerOrders->where('estado', '<>', 'cancelado')->sum('total'), 2),
                    'last_order' => $customerOrders->first()->created_at,
                ])
                ->sortByDesc('last_order')
                ->values()
                ->all(),
        ];
    }

    private function customerName(?User $customer): string
    {
        if (! $customer) {
            return 'Cliente no disponible';
        }

        $name = trim($customer->name);
        $lastNames = trim((string) $customer->apellidos);

        return $lastNames !== '' && ! str_ends_with(mb_strtolower($name), mb_strtolower($lastNames))
            ? $name.' '.$lastNames
            : $name;
    }
}
