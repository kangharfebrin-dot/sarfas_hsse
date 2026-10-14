<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $summary = $this->api->getDashboardSummary();
        $trends = $this->api->getDashboardTrends();

        // Detect if API response failed or was unreachable
        $apiError = empty($summary);

        // Strict zero defaults - NO fake or mock operational numbers
        $cards = $summary['cards'] ?? [
            'totalSarfas' => 0,
            'inspeksiBulanIni' => 0,
            'sarfasBaik' => 0,
            'goodConditionPct' => 0,
            'temuanBelumSelesai' => 0,
            'temuanKritis' => 0,
            'inspeksiTerlambat' => 0,
        ];

        $recentInspections = $summary['recentInspections'] ?? [];
        $highPriorityFindings = $summary['highPriorityFindings'] ?? [];
        $upcomingSchedules = $summary['upcomingSchedules'] ?? [];
        $recentActivity = $summary['recentActivity'] ?? [];
        $monthlyTrends = $trends['monthlyTrends'] ?? [];
        $categoryBreakdown = $trends['categoryBreakdown'] ?? [];

        return view('dashboard.index', compact(
            'cards',
            'recentInspections',
            'highPriorityFindings',
            'upcomingSchedules',
            'recentActivity',
            'monthlyTrends',
            'categoryBreakdown',
            'apiError'
        ));
    }
}
