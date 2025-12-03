<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Admin\Report\ReportRequest;
use App\Services\RevenueMetricsService;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Reports/Index', [
            'properties' => \App\Models\Property::query()->select('id', 'name')->get(),
        ]);
    }

    public function data(ReportRequest $request, RevenueMetricsService $metricsService)
    {
        $startDate = Date::parse($request->start_date);
        $endDate = Date::parse($request->end_date);
        $propertyId = $request->property_id;

        return response()->json(
            $metricsService->calculate($startDate, $endDate, $propertyId)
        );
    }

    public function export(ReportRequest $request, RevenueMetricsService $metricsService): StreamedResponse
    {
        $startDate = Date::parse($request->start_date);
        $endDate = Date::parse($request->end_date);
        $propertyId = $request->property_id;

        $data = $metricsService->calculate($startDate, $endDate, $propertyId);
        $chartData = $data['chart_data'];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="revenue-report-'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($chartData, $data) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Summary Header
            fputcsv($file, ['Souhrn']);
            fputcsv($file, ['Metrika', 'Hodnota']);
            fputcsv($file, ['Celkem rezervací', $data['total_bookings']]);
            fputcsv($file, ['Celkové tržby', number_format($data['total_revenue'], 2, ',', ' ').' Kč']);
            fputcsv($file, ['Obsazenost', $data['occupancy_rate'].' %']);
            fputcsv($file, ['ADR (Prům. cena za noc)', number_format($data['adr'], 2, ',', ' ').' Kč']);
            fputcsv($file, ['RevPAR', number_format($data['revpar'], 2, ',', ' ').' Kč']);
            fputcsv($file, []); // Empty line

            // Detailed Data
            fputcsv($file, ['Denní přehled']);
            fputcsv($file, ['Datum', 'Tržby', 'Obsazenost (pokoje/jednotky)']);

            foreach ($chartData as $row) {
                fputcsv($file, [
                    $row['date'],
                    number_format($row['revenue'], 2, ',', '.'),
                    $row['occupancy'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
