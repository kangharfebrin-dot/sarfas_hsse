<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class FindingController extends Controller
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
            'risk_level' => $request->input('risk_level'),
            'sarfas_id' => $request->input('sarfas_id'),
            'search' => $request->input('search'),
        ];

        $findings = $this->api->getFindings($filters);
        $sarfasList = $this->api->getSarfasList();

        return view('findings.index', compact('findings', 'sarfasList', 'filters'));
    }

    public function show($id)
    {
        $finding = $this->api->getFindingDetail($id);

        if (!$finding) {
            return redirect()->route('findings.index')->with('error', 'Temuan tidak ditemukan.');
        }

        $usersData = $this->api->getUsers();
        $users = $usersData['users'] ?? [];

        return view('findings.show', compact('finding', 'users'));
    }

    public function updateAction(Request $request, $id)
    {
        $payload = [
            'status' => $request->input('status'),
            'assigned_to' => $request->input('assigned_to'),
            'due_date' => $request->input('due_date'),
            'corrective_action_plan' => $request->input('corrective_action_plan'),
            'corrective_action_notes' => $request->input('corrective_action_notes'),
            'photo_after' => $request->input('photo_after'),
            'action_note' => $request->input('action_note'),
        ];

        $res = $this->api->updateFindingAction($id, $payload);

        if ($res['success'] ?? false) {
            return redirect()->route('findings.show', $id)->with('success', 'Tindakan korektif berhasil diperbarui.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal memperbarui tindakan temuan.');
    }

    public function verifyClose(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:VERIFY_CLOSE,REJECT',
            'verification_notes' => 'nullable',
        ]);

        $payload = [
            'action' => $request->input('action'),
            'verification_notes' => $request->input('verification_notes'),
            'photo_proof' => $request->input('photo_proof'),
        ];

        $res = $this->api->verifyCloseFinding($id, $payload);

        if ($res['success'] ?? false) {
            return redirect()->route('findings.show', $id)->with('success', $res['message']);
        }

        return back()->with('error', $res['message'] ?? 'Gagal memverifikasi temuan.');
    }
}
