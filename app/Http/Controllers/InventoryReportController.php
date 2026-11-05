<?php

namespace App\Http\Controllers;

use App\Exports\InventoryReportExport;
use App\Services\InventoryReportService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        return view('admin.reportes.inventario', ['report' => $service->generate()]);
    }

    public function pdf(Request $request, InventoryReportService $service): Response
    {
        $this->ensureStaff($request);
        $report = $service->generate();

        return Pdf::loadView('admin.reportes.exports.inventario', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download('reporte-inventario-'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request, InventoryReportService $service): BinaryFileResponse
    {
        $this->ensureStaff($request);

        return Excel::download(
            new InventoryReportExport($service->generate()),
            'reporte-inventario-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    private function ensureStaff(Request $request): void
    {
        $role = strtolower((string) $request->user()->rol?->nombre);
        abort_unless(in_array($role, ['admin', 'administrador', 'empleado'], true), 403);
    }
}
