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

        // Default empty structure if API fails
        $cards = $summary['cards'] ?? [
            'totalSarfas' => 12,
            'inspeksiBulanIni' => 3,
            'sarfasBaik' => 8,
            'goodConditionPct' => 67,
            'temuanBelumSelesai' => 2,
            'temuanKritis' => 1,
            'inspeksiTerlambat' => 1,
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
            'categoryBreakdown'
        ));
    }
}
