<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class SarfasController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'location_id' => $request->input('location_id'),
            'condition_status' => $request->input('condition_status'),
        ];

        $sarfasList = $this->api->getSarfasList($filters);
        $meta = $this->api->getSarfasMeta();
        $categories = $meta['categories'] ?? [];
        $locations = $meta['locations'] ?? [];

        return view('sarfas.index', compact('sarfasList', 'categories', 'locations', 'filters'));
    }

    public function show($id)
    {
        $sarfas = $this->api->getSarfasDetail($id);

        if (!$sarfas) {
            return redirect()->route('sarfas.index')->with('error', 'Sarfas tidak ditemukan.');
        }

        $meta = $this->api->getSarfasMeta();
        $categories = $meta['categories'] ?? [];
        $locations = $meta['locations'] ?? [];

        return view('sarfas.show', compact('sarfas', 'categories', 'locations'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'asset_code' => 'required',
            'name' => 'required',
            'category_id' => 'required|numeric',
            'location_id' => 'required|numeric',
            'brand_model' => 'nullable',
            'serial_number' => 'nullable',
            'capacity_specs' => 'nullable',
            'installation_date' => 'nullable|date',
            'condition_status' => 'nullable',
            'notes' => 'nullable',
        ]);

        $res = $this->api->createSarfas($payload);

        if ($res['success'] ?? false) {
            return redirect()->route('sarfas.index')->with('success', 'Sarfas berhasil didaftarkan ke sistem.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal menyimpan Sarfas.');
    }

    public function update(Request $request, $id)
    {
        $payload = $request->validate([
            'name' => 'required',
            'category_id' => 'required|numeric',
            'location_id' => 'required|numeric',
            'brand_model' => 'nullable',
            'serial_number' => 'nullable',
            'capacity_specs' => 'nullable',
            'installation_date' => 'nullable|date',
            'condition_status' => 'nullable',
            'notes' => 'nullable',
        ]);

        $res = $this->api->updateSarfas($id, $payload);

        if ($res['success'] ?? false) {
            return redirect()->route('sarfas.show', $id)->with('success', 'Data Sarfas berhasil diperbarui.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal memperbarui Sarfas.');
    }
}
