<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Models\Categoria;
use App\Services\InventoryReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryReportController extends Controller
{
    public function index(Request $request, InventoryReportService $service): View
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);

        return view('admin.reportes.inventario', [
            'report' => $service->generate($filters),
            'categories' => Categoria::where('status', true)->orderBy('nombre')->get(),
            'filters' => $filters,
        ]);
    }

    public function pdf(Request $request, InventoryReportService $service): Response
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);
        $report = $service->generate($filters);

        return Pdf::loadView('admin.reportes.exports.inventario', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download('reporte-inventario-'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request, InventoryReportService $service): BinaryFileResponse
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);

        return Excel::download(
            new InventoryReportExport($service->generate($filters)),
            'reporte-inventario-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * @return array{category_id: int|null, status: string|null, recent: bool, period: string|null, from: CarbonImmutable|null, to: CarbonImmutable|null}
     */
    private function validatedFilters(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categorias,id_categorias'],
            'status' => ['nullable', 'in:minimos,agotados'],
            'recent' => ['nullable', 'boolean'],
            'period' => ['nullable', 'in:today,yesterday,last_week,last_month'],
        ]);
        [$from, $to] = $this->periodBounds($data['period'] ?? null);

        return [
            'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : null,
            'status' => $data['status'] ?? null,
            'recent' => $request->boolean('recent'),
            'period' => $data['period'] ?? null,
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * @return array{0: CarbonImmutable|null, 1: CarbonImmutable|null}
     */
    private function periodBounds(?string $period): array
    {
        $today = CarbonImmutable::now();

        return match ($period) {
            'today' => [$today->startOfDay(), $today->endOfDay()],
            'yesterday' => [$today->subDay()->startOfDay(), $today->subDay()->endOfDay()],
            'last_week' => [$today->subWeek()->startOfWeek(), $today->subWeek()->endOfWeek()],
            'last_month' => [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()],
            default => [null, null],
        };
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
