<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class InspectionController extends Controller
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
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $inspections = $this->api->getInspections($filters);
        $sarfasList = $this->api->getSarfasList(['is_active' => true]);

        return view('inspections.index', compact('inspections', 'sarfasList', 'filters'));
    }

    /**
     * Wizard Step 1: Select Sarfas and Checklist Template
     */
    public function create(Request $request)
    {
        $selectedSarfasId = $request->input('sarfas_id');
        $sarfasList = $this->api->getSarfasList(['is_active' => true]);
        $templates = $this->api->getChecklistTemplates();

        $selectedSarfas = null;
        if ($selectedSarfasId) {
            $selectedSarfas = $this->api->getSarfasDetail($selectedSarfasId);
        }

        return view('inspections.create', compact('sarfasList', 'templates', 'selectedSarfas', 'selectedSarfasId'));
    }

    /**
     * Main Interactive Inspection Form
     */
    public function form(Request $request, $id = null)
    {
        $inspection = null;
        $sarfas = null;
        $template = null;

        if ($id) {
            // Edit existing draft or resume inspection
            $inspection = $this->api->getInspectionDetail($id);
            if (!$inspection) {
                return redirect()->route('inspections.index')->with('error', 'Inspeksi tidak ditemukan.');
            }
            $sarfas = $this->api->getSarfasDetail($inspection['sarfas_id']);
            $template = $this->api->getChecklistTemplateDetail($inspection['template_id']);
        } else {
            // Initiating new inspection from query params
            $sarfasId = $request->input('sarfas_id');
            $templateId = $request->input('template_id');

            if (!$sarfasId || !$templateId) {
                return redirect()->route('inspections.create')->with('warning', 'Pilih Sarfas dan Template Checklist terlebih dahulu.');
            }

            $sarfas = $this->api->getSarfasDetail($sarfasId);
            $template = $this->api->getChecklistTemplateDetail($templateId);

            if (!$sarfas || !$template) {
                return redirect()->route('inspections.create')->with('error', 'Data Sarfas atau Template Checklist tidak valid.');
            }

            if (!$sarfas['is_active']) {
                return redirect()->route('inspections.create')->with('error', 'Inspeksi tidak dapat dilakukan pada sarfas berstatus nonaktif.');
            }
        }

        return view('inspections.form', compact('inspection', 'sarfas', 'template', 'id'));
    }

    /**
     * Save Draft Action
     */
    public function saveDraft(Request $request, $id = null)
    {
        $payload = [
            'sarfas_id' => $request->input('sarfas_id'),
            'template_id' => $request->input('template_id'),
            'inspection_date' => $request->input('inspection_date', date('Y-m-d')),
            'general_notes' => $request->input('general_notes'),
            'answers' => $request->input('answers', []),
        ];

        $res = $this->api->saveInspectionDraft($payload, $id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            $inspId = $res['data']['inspection_id'] ?? $id;
            return redirect()->route('inspections.form', $inspId)->with('success', 'Draft inspeksi berhasil disimpan. Anda dapat melanjutkannya kapan saja.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal menyimpan draft inspeksi.');
    }

    /**
     * Submit Final Inspection Action
     */
    public function submit(Request $request, $id)
    {
        $payload = [
            'general_notes' => $request->input('general_notes'),
            'inspector_signature' => $request->input('inspector_signature', 'DigitalSignature_' . session('auth_user.name', 'Inspector')),
            'answers' => $request->input('answers', []),
        ];

        $res = $this->api->submitInspection($id, $payload);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return redirect()->route('inspections.show', $id)->with('success', 'Hasil inspeksi berhasil dikirim dan diverifikasi ke sistem HSSE.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal mengirimkan inspeksi.');
    }

    /**
     * View Inspection Detail & Summary
     */
    public function show($id)
    {
        $inspection = $this->api->getInspectionDetail($id);

        if (!$inspection) {
            return redirect()->route('inspections.index')->with('error', 'Inspeksi tidak ditemukan.');
        }

        return view('inspections.show', compact('inspection'));
    }

    /**
     * Review / Approve Inspection
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'verification_notes' => 'nullable',
        ]);

        $payload = [
            'action' => $request->input('action'),
            'verification_notes' => $request->input('verification_notes'),
        ];

        $res = $this->api->reviewInspection($id, $payload);

        if ($res['success'] ?? false) {
            return redirect()->route('inspections.show', $id)->with('success', $res['message']);
        }

        return back()->with('error', $res['message'] ?? 'Gagal memproses verifikasi inspeksi.');
    }
}
