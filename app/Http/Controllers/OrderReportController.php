<?php

namespace App\Http\Controllers;

use App\Exports\OrderReportExport;
use App\Services\OrderReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderReportController extends Controller
{
    public function index(Request $request, OrderReportService $service): View
    {
        $this->ensureStaff($request);
        [$from, $to, $period] = $this->periodBounds($request);

        $report = $service->generate($from, $to);
        $report['customers'] = $this->paginateRows($report['customers'], $request);

        return view('admin.reportes.pedidos', ['report' => $report, 'period' => $period]);
    }

    public function pdf(Request $request, OrderReportService $service): Response
    {
        $this->ensureStaff($request);
        [$from, $to] = $this->periodBounds($request);

        return Pdf::loadView('admin.reportes.exports.pedidos', ['report' => $service->generate($from, $to)])
            ->setPaper('a4', 'landscape')->download('reporte-pedidos-'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request, OrderReportService $service): BinaryFileResponse
    {
        $this->ensureStaff($request);
        [$from, $to] = $this->periodBounds($request);

        return Excel::download(new OrderReportExport($service->generate($from, $to)), 'reporte-pedidos-'.now()->format('Y-m-d').'.xlsx');
    }

    /** @return array{0: CarbonImmutable|null, 1: CarbonImmutable|null, 2: string|null} */
    private function periodBounds(Request $request): array
    {
        $period = $request->validate(['period' => ['nullable', 'in:today,yesterday,last_week,last_month']])['period'] ?? null;
        $today = CarbonImmutable::now();

        [$from, $to] = match ($period) {
            'today' => [$today->startOfDay(), $today->endOfDay()],
            'yesterday' => [$today->subDay()->startOfDay(), $today->subDay()->endOfDay()],
            'last_week' => [$today->subWeek()->startOfWeek(), $today->subWeek()->endOfWeek()],
            'last_month' => [$today->subMonth()->startOfMonth(), $today->subMonth()->endOfMonth()],
            default => [null, null],
        };

        return [$from, $to, $period];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return LengthAwarePaginator<array<string, mixed>>
     */
    private function paginateRows(array $rows, Request $request): LengthAwarePaginator
    {
        $pageName = 'orders_page';
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ],
        );
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
