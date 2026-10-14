<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class ChecklistTemplateController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $templates = $this->api->getChecklistTemplates();
        $meta = $this->api->getSarfasMeta();
        $categories = $meta['categories'] ?? [];

        return view('master-checklist.index', compact('templates', 'categories'));
    }

    public function show($id)
    {
        $template = $this->api->getChecklistTemplateDetail($id);

        if (!$template) {
            return redirect()->route('master-checklist.index')->with('error', 'Template checklist tidak ditemukan.');
        }

        return view('master-checklist.show', compact('template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_code' => 'required',
            'title' => 'required',
            'category_id' => 'required|numeric',
            'inspection_frequency' => 'required',
            'description' => 'nullable',
        ]);

        $payload = $request->all();
        $res = $this->api->createChecklistTemplate($payload);

        if ($res['success'] ?? false) {
            return redirect()->route('master-checklist.index')->with('success', 'Template checklist berhasil ditambahkan.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal membuat template.');
    }
}
