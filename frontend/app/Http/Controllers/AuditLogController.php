<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $filters = [
            'module' => $request->input('module'),
            'user_id' => $request->input('user_id'),
        ];

        $logs = $this->api->getAuditLogs($filters);

        return view('audit-logs.index', compact('logs', 'filters'));
    }
}
