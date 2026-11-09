<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Services\SalesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalesReportController extends Controller
{
    public function index(Request $request, SalesReportService $service): View
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);
        $report = $service->generate($filters['from'], $filters['to']);

        return view('admin.reportes.ventas', [
            'report' => $report,
            'startDate' => $filters['from']->format('Y-m-d'),
            'endDate' => $filters['to']->format('Y-m-d'),
        ]);
    }

    public function pdf(Request $request, SalesReportService $service): Response
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);
        $report = $service->generate($filters['from'], $filters['to']);

        return Pdf::loadView('admin.reportes.exports.ventas', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download('reporte-ventas-'.$filters['from']->format('Y-m-d').'-'.$filters['to']->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request, SalesReportService $service): BinaryFileResponse
    {
        $this->ensureStaff($request);
        $filters = $this->validatedFilters($request);
        $report = $service->generate($filters['from'], $filters['to']);

        return Excel::download(
            new SalesReportExport($report),
            'reporte-ventas-'.$filters['from']->format('Y-m-d').'-'.$filters['to']->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable, period: string|null}
     */
    private function validatedFilters(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'period' => ['nullable', 'in:today,yesterday,last_week,last_month'],
        ]);

        $today = CarbonImmutable::now();
        [$from, $to] = match ($data['period'] ?? null) {
            'today' => [$today->startOfDay(), $today->endOfDay()],
            'yesterday' => [$today->subDay()->startOfDay(), $today->subDay()->endOfDay()],
            'last_week' => [$today->subWeek()->startOfWeek(), $today->subWeek()->endOfWeek()],
            'last_month' => [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()],
            default => [
                isset($data['start_date'])
                    ? CarbonImmutable::createFromFormat('!Y-m-d', $data['start_date'])->startOfDay()
                    : $today->startOfMonth(),
                isset($data['end_date'])
                    ? CarbonImmutable::createFromFormat('!Y-m-d', $data['end_date'])->endOfDay()
                    : $today->endOfDay(),
            ],
        };

        return ['from' => $from, 'to' => $to, 'period' => $data['period'] ?? null];
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
