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
        $client = Http::timeout(8)->acceptJson();
        if ($token = $this->getToken()) {
            $client = $client->withToken($token);
        }
        return $client;
    }

    /**
     * Standardize response from Express API
     */
    protected function handleMutationResponse($response, string $defaultFallbackMsg = 'Operasi gagal diproses'): array
    {
        $statusCode = $response->status();
        $body = $response->json();

        if ($response->successful()) {
            return $body ?? ['success' => true, 'message' => 'Berhasil'];
        }

        // Handle Token Expiry or Invalidation
        if ($statusCode === 401) {
            Session::forget(['jwt_token', 'auth_user']);
            return [
                'success' => false,
                'status_code' => 401,
                'message' => $body['message'] ?? 'Sesi autentikasi telah berakhir. Silakan login kembali.'
            ];
        }

        // Handle Forbidden
        if ($statusCode === 403) {
            return [
                'success' => false,
                'status_code' => 403,
                'message' => $body['message'] ?? 'Akses ditolak: Anda tidak memiliki hak wewenang untuk tindakan ini.'
            ];
        }

        // Handle Validation or Bad Request
        if ($statusCode === 400 || $statusCode === 422) {
            $msg = $body['message'] ?? $defaultFallbackMsg;
            if (!empty($body['errors']) && is_array($body['errors'])) {
                $msg .= ' (' . implode('; ', array_slice($body['errors'], 0, 3)) . ')';
            }
            return [
                'success' => false,
                'status_code' => $statusCode,
                'message' => $msg,
                'errors' => $body['errors'] ?? null
            ];
        }

        // Handle Rate Limiter
        if ($statusCode === 429) {
            return [
                'success' => false,
                'status_code' => 429,
                'message' => 'Terlalu banyak permintaan ke backend API. Harap tunggu beberapa saat.'
            ];
        }

        return [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $body['message'] ?? $defaultFallbackMsg
        ];
    }

    /**
     * Check backend health status
     */
    public function checkHealth(): array
    {
        try {
            $healthUrl = preg_replace('/\/api\/v1$/', '', $this->baseUrl) . '/health';
            $response = Http::timeout(4)->acceptJson()->get($healthUrl);
            if ($response->successful()) {
                return ['status' => 'healthy', 'data' => $response->json()];
            }
            return ['status' => 'unhealthy', 'message' => 'Status kode backend: ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'offline', 'message' => 'Backend offline: ' . $e->getMessage()];
        }
    }

    /**
     * Authenticate user with Express REST API
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = Http::timeout(8)->acceptJson()->post("{$this->baseUrl}/auth/login", [
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
                'message' => 'Gagal terhubung ke backend Express API (Port 3001). Pastikan service backend aktif.'
            ];
        }
    }

    /**
     * Logout and revoke session
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
            Log::info('Express API unreachable for demo accounts.');
        }

        return [];
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
            if ($response->status() === 401) {
                Session::forget(['jwt_token', 'auth_user']);
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
            return $this->handleMutationResponse($response, 'Gagal menambahkan sarfas.');
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
            return $this->handleMutationResponse($response, 'Gagal memperbarui sarfas.');
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
            return $this->handleMutationResponse($response, 'Gagal membuat template checklist.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
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
            return $this->handleMutationResponse($response, 'Gagal menyimpan draft inspeksi.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Submit Inspection
     */
    public function submitInspection($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/inspections/{$id}/submit", $payload);
            return $this->handleMutationResponse($response, 'Gagal mengirimkan inspeksi.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Review Inspection
     */
    public function reviewInspection($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/inspections/{$id}/review", $payload);
            return $this->handleMutationResponse($response, 'Gagal memverifikasi inspeksi.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
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
     * Update Action on Finding
     */
    public function updateFindingAction($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/findings/{$id}/actions", $payload);
            return $this->handleMutationResponse($response, 'Gagal memperbarui progres temuan.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Verify and Close Finding
     */
    public function verifyCloseFinding($id, array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/findings/{$id}/verify", $payload);
            return $this->handleMutationResponse($response, 'Gagal memverifikasi penyelesaian temuan.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Inspection Schedules List
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
            return $this->handleMutationResponse($response, 'Gagal menambahkan jadwal.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
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
            return $this->handleMutationResponse($response, 'Gagal membuat pengguna.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Update User
     */
    public function updateUser($id, array $data): array
    {
        try {
            $response = $this->client()->patch("{$this->baseUrl}/users/{$id}", $data);
            return $this->handleMutationResponse($response, 'Gagal memperbarui pengguna.');
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Koneksi ke backend gagal: ' . $e->getMessage()];
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
