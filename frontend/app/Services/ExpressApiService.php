<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ExpressApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.express.url', 'http://localhost:3001/api/v1'), '/');
    }

    /**
     * Get active authentication token from session
     */
    protected function getToken(): ?string
    {
        return Session::get('jwt_token');
    }

    /**
     * Build base HTTP client with timeout and authorization
     */
    protected function client()
    {
        $client = Http::timeout(6)->acceptJson();
        if ($token = $this->getToken()) {
            $client = $client->withToken($token);
        }
        return $client;
    }

    /**
     * Authenticate user with Express REST API
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = Http::timeout(6)->post("{$this->baseUrl}/auth/login", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Session::put('jwt_token', $data['token'] ?? null);
                Session::put('auth_user', $data['user'] ?? null);
                return ['success' => true, 'data' => $data];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Login gagal. Periksa kembali kredensial Anda.'
            ];
        } catch (\Exception $e) {
            Log::warning('Express API connection failed on login: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke backend Express API (Port 3001). Pastikan backend aktif.'
            ];
        }
    }

    /**
     * Logout and destroy session
     */
    public function logout(): void
    {
        try {
            if ($this->getToken()) {
                $this->client()->post("{$this->baseUrl}/auth/logout");
            }
        } catch (\Exception $e) {
            Log::warning('Express API logout error: ' . $e->getMessage());
        } finally {
            Session::forget(['jwt_token', 'auth_user']);
        }
    }

    /**
     * Get demo accounts for quick switcher
     */
    public function getDemoAccounts(): array
    {
        try {
            $response = Http::timeout(4)->get("{$this->baseUrl}/auth/demo-accounts");
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::info('Express API unreachable for demo accounts, returning fallback.');
        }

        // Fallback demo accounts if server offline
        return [
            ['name' => 'Budi Santoso (Demo)', 'email' => 'superadmin@pertamina.demo', 'role_name' => 'Super Admin', 'role_slug' => 'super_admin', 'default_password' => 'password123'],
            ['name' => 'Raden Harfebrin (Demo)', 'email' => 'admin.hsse@pertamina.demo', 'role_name' => 'Admin HSSE', 'role_slug' => 'admin_hsse', 'default_password' => 'password123'],
            ['name' => 'Eko Prasetyo (Demo)', 'email' => 'inspektor.maos@pertamina.demo', 'role_name' => 'Inspektor Lapangan', 'role_slug' => 'inspektor', 'default_password' => 'password123'],
            ['name' => 'Dwi Cahyono (Demo)', 'email' => 'verifikator.hsse@pertamina.demo', 'role_name' => 'Approver / Verifikator', 'role_slug' => 'approver', 'default_password' => 'password123'],
        ];
    }

    /**
     * Dashboard Summary
     */
    public function getDashboardSummary(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/dashboard/summary");
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API dashboard summary error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Dashboard Trends
     */
    public function getDashboardTrends(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/dashboard/inspection-trends");
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API dashboard trends error: ' . $e->getMessage());
        }
        return ['monthlyTrends' => [], 'categoryBreakdown' => []];
    }

    /**
     * Sarfas List with filters
     */
    public function getSarfasList(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/sarfas", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getSarfasList error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Sarfas Details
     */
    public function getSarfasDetail($id): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/sarfas/{$id}");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Exception $e) {
            Log::warning("Express API getSarfasDetail({$id}) error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Sarfas Meta (Categories & Locations)
     */
    public function getSarfasMeta(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/sarfas/meta");
            if ($response->successful()) {
                return $response->json('data') ?? ['categories' => [], 'locations' => []];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getSarfasMeta error: ' . $e->getMessage());
        }
        return ['categories' => [], 'locations' => []];
    }

    /**
     * Create Sarfas
     */
    public function createSarfas(array $data): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/sarfas", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Update Sarfas
     */
    public function updateSarfas($id, array $data): array
    {
        try {
            $response = $this->client()->patch("{$this->baseUrl}/sarfas/{$id}", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Master Checklist Templates
     */
    public function getChecklistTemplates(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/checklist-templates");
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getChecklistTemplates error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Single Template with Sections and Items
     */
    public function getChecklistTemplateDetail($id): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/checklist-templates/{$id}");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Exception $e) {
            Log::warning("Express API getChecklistTemplateDetail({$id}) error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Create Checklist Template
     */
    public function createChecklistTemplate(array $data): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/checklist-templates", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal membuat template checklist.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Inspections List
     */
    public function getInspections(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/inspections", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getInspections error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Inspection Detail with answers
     */
    public function getInspectionDetail($id): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/inspections/{$id}");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Exception $e) {
            Log::warning("Express API getInspectionDetail({$id}) error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Save Inspection Draft
     */
    public function saveInspectionDraft(array $payload, $id = null): array
    {
        try {
            $url = $id ? "{$this->baseUrl}/inspections/{$id}/draft" : "{$this->baseUrl}/inspections/draft";
            $response = $this->client()->post($url, $payload);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal menyimpan draft.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Submit Inspection
     */
    public function submitInspection($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/inspections/{$id}/submit", $payload);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal mengirim inspeksi.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Review Inspection
     */
    public function reviewInspection($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/inspections/{$id}/review", $payload);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal memverifikasi inspeksi.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Findings List
     */
    public function getFindings(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/findings", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getFindings error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Finding Detail
     */
    public function getFindingDetail($id): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/findings/{$id}");
            if ($response->successful()) {
                return $response->json('data');
            }
        } catch (\Exception $e) {
            Log::warning("Express API getFindingDetail({$id}) error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Update Finding Action Progress
     */
    public function updateFindingAction($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/findings/{$id}/actions", $payload);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal memperbarui progres temuan.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify and Close Finding
     */
    public function verifyCloseFinding($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/findings/{$id}/verify", $payload);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal memverifikasi temuan.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Schedules List
     */
    public function getSchedules(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/inspection-schedules", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getSchedules error: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Create Schedule
     */
    public function createSchedule(array $data): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/inspection-schedules", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal menambahkan jadwal.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reports Data
     */
    public function getReportsData(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/reports/inspections", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getReportsData error: ' . $e->getMessage());
        }
        return ['records' => [], 'summary' => []];
    }

    /**
     * Users List
     */
    public function getUsers(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/users");
            if ($response->successful()) {
                return $response->json('data') ?? ['users' => [], 'roles' => []];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getUsers error: ' . $e->getMessage());
        }
        return ['users' => [], 'roles' => []];
    }

    /**
     * Create User
     */
    public function createUser(array $data): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/users", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal membuat pengguna.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update User
     */
    public function updateUser($id, array $data): array
    {
        try {
            $response = $this->client()->patch("{$this->baseUrl}/users/{$id}", $data);
            return $response->json() ?? ['success' => false, 'message' => 'Gagal memperbarui pengguna.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Audit Logs
     */
    public function getAuditLogs(array $filters = []): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/audit-logs", array_filter($filters));
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Express API getAuditLogs error: ' . $e->getMessage());
        }
        return [];
    }
}
