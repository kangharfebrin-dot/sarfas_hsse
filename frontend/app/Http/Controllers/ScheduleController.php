<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->input('status'),
            'sarfas_id' => $request->input('sarfas_id'),
            'month' => $request->input('month', date('m')),
            'year' => $request->input('year', date('Y')),
        ];

        $schedules = $this->api->getSchedules($filters);
        $sarfasList = $this->api->getSarfasList(['is_active' => true]);
        $templates = $this->api->getChecklistTemplates();
        $usersData = $this->api->getUsers();
        $users = $usersData['users'] ?? [];

        return view('schedules.index', compact('schedules', 'sarfasList', 'templates', 'users', 'filters'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'title' => 'required',
            'sarfas_id' => 'required|numeric',
            'template_id' => 'required|numeric',
            'assigned_user_id' => 'required|numeric',
            'scheduled_date' => 'required|date',
            'frequency' => 'required',
            'notes' => 'nullable',
        ]);

        $res = $this->api->createSchedule($payload);

        if ($res['success'] ?? false) {
            return redirect()->route('schedules.index')->with('success', 'Jadwal inspeksi berhasil ditambahkan.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal menambahkan jadwal.');
    }
}
