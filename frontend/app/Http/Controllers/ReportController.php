<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'category_id' => $request->input('category_id'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
            'sarfas_id' => $request->input('sarfas_id'),
        ];

        $reportData = $this->api->getReportsData($filters);
        $meta = $this->api->getSarfasMeta();
        $categories = $meta['categories'] ?? [];
        $locations = $meta['locations'] ?? [];
        $sarfasList = $this->api->getSarfasList();

        $records = $reportData['records'] ?? [];
        $summary = $reportData['summary'] ?? [
            'totalInspections' => count($records),
            'completedInspections' => 0,
            'goodConditionCount' => 0,
            'actionNeededCount' => 0,
            'criticalCount' => 0,
            'complianceRate' => 0,
        ];

        return view('reports.index', compact('records', 'summary', 'categories', 'locations', 'sarfasList', 'filters', 'reportData'));
    }

    /**
     * Export reports (printable PDF view or CSV download based on active filter)
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'print'); // 'print' or 'csv'
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'category_id' => $request->input('category_id'),
            'location_id' => $request->input('location_id'),
            'status' => $request->input('status'),
            'sarfas_id' => $request->input('sarfas_id'),
        ];

        $reportData = $this->api->getReportsData($filters);
        $records = $reportData['records'] ?? [];
        $summary = $reportData['summary'] ?? [];

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Laporan_Inspeksi_Sarfas_FT_Maos_' . date('Ymd_His') . '.csv"',
            ];

            // Helper to prevent CSV Formula Injection (Excel / LibreOffice DDE attack)
            $sanitizeCsv = function ($val) {
                $str = (string) ($val ?? '-');
                if (preg_match('/^[\=\+\-\@\t\r]/', $str)) {
                    return "'" . $str;
                }
                return $str;
            };

            $callback = function() use ($records, $sanitizeCsv) {
                $file = fopen('php://output', 'w');
                // Add UTF-8 BOM for Microsoft Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($file, ['No', 'No Inspeksi', 'Tanggal', 'Kode Sarfas', 'Nama Sarfas', 'Kategori', 'Lokasi', 'Inspektor', 'Status', 'Kondisi Hasil', 'Total Temuan']);

                foreach ($records as $idx => $r) {
                    fputcsv($file, [
                        $idx + 1,
                        $sanitizeCsv($r['inspection_no'] ?? '-'),
                        $sanitizeCsv($r['inspection_date'] ?? '-'),
                        $sanitizeCsv($r['asset_code'] ?? '-'),
                        $sanitizeCsv($r['sarfas_name'] ?? '-'),
                        $sanitizeCsv($r['category_name'] ?? '-'),
                        $sanitizeCsv($r['location_name'] ?? '-'),
                        $sanitizeCsv($r['inspector_name'] ?? '-'),
                        $sanitizeCsv($r['status'] ?? '-'),
                        $sanitizeCsv($r['summary_condition'] ?? '-'),
                        (int) ($r['total_findings'] ?? 0),
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Cetak Dokumen / Simpan PDF Browser View
        return view('reports.print', compact('records', 'summary', 'filters', 'reportData'));
    }
}
