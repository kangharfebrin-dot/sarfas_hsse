# Checklist Sarfas HSSE — PT Pertamina Patra Niaga (FT Maos)

Sistem Informasi Digitalisasi Inspeksi, Pemeriksaan, Pemantauan, dan Pelaporan Kondisi Sarana dan Fasilitas (Sarfas) HSSE di lingkungan **PT Pertamina Patra Niaga — Fuel Terminal Maos (Integrated Terminal Cilacap)**.

---

## 1. Arsitektur Teknologi & Pemisahan Tanggung Jawab

Aplikasi ini dibangun menggunakan arsitektur *decoupled* yang memisahkan layer presentasi (Frontend), logika bisnis & REST API (Backend), dan database relasional:

```
[ Pengguna / Browser ]
          │ (HTTP / Session Cookie)
          ▼
┌───────────────────────────────────────────────┐
│              FRONTEND (Laravel 13)            │
│   • PHP 8.3+          • Blade Templates       │
│   • Tailwind CSS (v4) • Vite 8                │
│   • Alpine.js (v3)    • Chart.js (v4)         │
│   • ExpressApiService (Server-to-Server HTTP) │
│   Port: http://localhost:8000                 │
└───────────────────────┬───────────────────────┘
                        │ (REST API JSON / Bearer JWT)
                        ▼
┌───────────────────────────────────────────────┐
│          BACKEND API (Node.js + Express)      │
│   • Node.js 20.19+ / 22+ / 24+                │
│   • Express.js 4.21   • Helmet Security       │
│   • Rate Limiting     • Strict Origin CORS    │
│   • Anti-IDOR Scoping • Token Revocation List │
│   • Knex Query Engine • Audit Trail Logger    │
│   Port: http://localhost:3001                 │
└───────────────────────┬───────────────────────┘
                        │ (mysql2 / Knex Query Builder)
                        ▼
┌───────────────────────────────────────────────┐
│           DATABASE (WAMPServer MySQL)         │
│   • MariaDB / MySQL 5.7+ / 8.0+               │
│   • InnoDB Engine      • utf8mb4 Charset      │
│   • 14 Relational Tables                      │
│   Port: localhost:3306                        │
└───────────────────────────────────────────────┘
```

> **Prinsip Arsitektur Utama:**
> - **Laravel 13** hanya bertindak sebagai antarmuka pengguna (UI/UX), session guard server-side, dan view rendering.
> - **Express.js** merupakan **satu-satunya gerbang** yang memiliki hak akses query ke database MySQL dan mengeksekusi logika bisnis (validasi, perhitungan jadwal berkala, transisi status temuan, dan logging audit).
> - Tidak ada koneksi database langsung di Laravel. Kredensial database tidak pernah terekspos ke frontend atau browser pengguna.

---

## 2. Fitur Keamanan & Integritas Data (Security Hardening)

Aplikasi ini telah melalui audit teknis dan pengerasan keamanan menyeluruh:

1. **Zero Hardcoded Secrets & Fail-Fast Startup:**
   - Fallback JWT secret bawaan telah dihilangkan total. Server backend akan menolak startup pada lingkungan produksi jika `JWT_SECRET` tidak dikonfigurasi atau kurang dari 32 karakter.
2. **Revocable JWT Tokens (Logout Denylist):**
   - Setiap aksi logout mendaftarkan JWT token ke *in-memory revocation list* aktif dengan pembersihan otomatis setelah masa berlaku token berakhir (24 jam).
3. **Proteksi Brute-Force & HTTP Headers:**
   - Endpoint login diproteksi rate limiter (maksimal 30 percobaan per 15 menit per IP).
   - Global rate limiter (1000 request per 15 menit).
   - Integrasi `helmet` untuk proteksi Cross-Site Scripting (XSS), Content Security Policy, and header framing.
4. **CORS Ketat (Strict Origin Whitelist):**
   - Menolak request origin yang tidak terdaftar di variabel lingkungan `CORS_ORIGIN`. Request internal server-to-server (tanpa origin header dari Laravel) tetap diizinkan secara aman.
5. **Isolasi Mode Demo:**
   - Endpoint `/api/v1/auth/demo-accounts` dan quick demo login hanya aktif bila `DEMO_MODE=true` dan `NODE_ENV !== 'production'`.
   - Hanya mengembalikan 4 akun demo terdaftar (tidak pernah membaca seluruh tabel user atau mengekspos hash kata sandi).
6. **Pencegahan IDOR (Insecure Direct Object Reference) & Hak Kepemilikan Data:**
   - Draft inspeksi hanya dapat disunting atau dikirimkan oleh inspektor pembuatnya (atau Super Admin / Admin HSSE).
   - Inspektor lain diblokir dengan status HTTP 403 Forbidden bila mencoba memanipulasi ID draft pengguna lain.
7. **Perlindungan Akun Super Admin:**
   - Akun Super Admin dilindungi dari penurunan hak akses (*role demotion*) atau penonaktifan tidak sengaja.
8. **Validasi Formulir HSSE Lapangan:**
   - Whitelist template checklist: Item yang bukan bagian dari template inspeksi langsung ditolak dengan HTTP 400.
   - Wajib unggah foto dokumentasi fisik untuk item dengan kriteria `requires_photo: true`.
   - Wajib catatan ketidaksesuaian (minimal 5 karakter) untuk setiap butir dengan kondisi `TIDAK_BAIK`.
   - Pencegahan pengiriman ganda (*idempotent submit protection*).
9. **Kalkulasi Jadwal Berkala Akurat:**
   - Mengikuti frekuensi checklist: `HARIAN` (+1 hari), `MINGGUAN` (+7 hari), `BULANAN` (+1 bulan), `TRIWULAN` (+3 bulan), `SEMESTER` (+6 bulan), dan `TAHUNAN` (+1 tahun) dengan perhitungan batas akhir bulan dan tahun kabisat yang benar.
10. **Akurasi Dashboard & Proteksi Ekspor:**
    - Dashboard tidak lagi menampilkan fallback data palsu jika API backend tidak merespons; status gangguan ditampilkan secara transparan via alert banner.
    - Query laporan mengeliminasi bottleneck N+1 melalui agregasi SQL tunggal (`LEFT JOIN` + `COUNT`).
    - Ekspor CSV disanitasi dari potensi *Spreadsheet Formula Injection* (`=, +, -, @, \t, \r`).

---

## 3. Prasyarat Sistem & Dependensi

| Komponen | Persyaratan Minimum | Versi yang Digunakan |
| :--- | :--- | :--- |
| **PHP** | `>= 8.3` (ekstensi: `curl`, `mbstring`, `openssl`, `pdo`, `xml`) | PHP 8.3 / 8.4 |
| **Laravel** | `^13.17` (Laravel 13) | Laravel 13.x |
| **Node.js** | `>= 20.19.0` atau `>= 22.12.0` (syarat Vite 8) | Node.js 20.19+ / 24+ |
| **Vite** | `^8.0.0` | Vite 8.3.x |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ (WAMPServer) | MySQL 5.7 / 8.0 via WAMP |
| **Web Server** | Port `8000` (Laravel) & Port `3001` (Express API) | - |

---

## 4. Panduan Instalasi & Menjalankan (Windows PowerShell & WAMPServer)

### A. Persiapan Database WAMPServer
1. Pastikan **WAMPServer** sudah berjalan (ikon WAMP pada taskbar berwarna hijau).
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) atau terminal MySQL, lalu buat database:
   ```sql
   CREATE DATABASE checklist_sarfas_hsse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   *(Port standar WAMPServer MySQL adalah `3306` dengan username `root` tanpa password).*

---

### B. Menyiapkan & Menjalankan Backend (Express API)

1. Buka jendela **Windows PowerShell**, lalu masuk ke direktori backend:
   ```powershell
   cd e:\Magang\Checklist_Sarfas_HSSE\backend
   ```
2. Pasang dependensi Node.js:
   ```powershell
   npm install
   ```
3. Buat file `.env` dari template `.env.example`:
   ```powershell
   Copy-Item .env.example .env
   ```
   *Pastikan parameter database di `backend/.env` sesuai dengan WAMPServer:*
   ```env
   NODE_ENV=development
   PORT=3001
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=checklist_sarfas_hsse
   JWT_SECRET=pertamina_ft_maos_hsse_jwt_secret_production_ready_2026_super_secure
   JWT_EXPIRES_IN=24h
   CORS_ORIGIN=http://localhost:8000,http://127.0.0.1:8000
   DEMO_MODE=true
   ```
4. Jalankan migrasi tabel database Knex:
   ```powershell
   npm run migrate
   ```
5. Jalankan seed data master sarfas dan fixture operasional FT Maos:
   ```powershell
   npm run seed
   ```
6. **Jalankan Automated Test Suite Backend:**
   ```powershell
   npm test
   ```
   *Seluruh 22 test assertion (koneksi, auth, RBAC, IDOR, checklist, submit, report) harus berstatus `✓ PASS` dengan exit code 0.*
7. Jalankan server Express API:
   ```powershell
   npm run dev
   ```
   *API akan aktif di `http://localhost:3001` (Health check: `http://localhost:3001/health`).*

---

### C. Menyiapkan & Menjalankan Frontend (Laravel 13)

1. Buka jendela **PowerShell baru**, lalu masuk ke direktori frontend:
   ```powershell
   cd e:\Magang\Checklist_Sarfas_HSSE\frontend
   ```
2. Pasang dependensi Composer dan NPM:
   ```powershell
   composer install
   npm install
   ```
3. Buat file `.env` frontend:
   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```
   *Pastikan URL API Express di `frontend/.env` mengarah ke port backend:*
   ```env
   APP_NAME="Checklist Sarfas HSSE"
   APP_ENV=local
   APP_URL=http://localhost:8000
   EXPRESS_API_URL=http://localhost:3001/api/v1
   DEMO_MODE=true
   ```
4. Jalankan pengujian otomatis Laravel:
   ```powershell
   php vendor/bin/phpunit
   ```
   *Seluruh 6 Feature & Unit test authentication redirect dan login page harus berstatus `passed`.*
5. Build aset frontend (Tailwind CSS v4 & Alpine.js via Vite 8):
   ```powershell
   npm run build
   ```
6. Jalankan server web Laravel:
   ```powershell
   php artisan serve --port=8000
   ```
   *Buka browser di **`http://localhost:8000`**.*

---

## 5. Akun Demonstrasi (1-Click Demo Login)

Halaman login (`http://localhost:8000/login`) menyediakan **1-Click Demo Switcher** untuk simulasi dan audit alur peran pengguna secara cepat:

| Role | Email | Password | Cakupan Wewenang |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@pertamina.demo` | `password123` | Akses penuh sistem, kelola user, konfigurasi server, dan audit log lengkap. |
| **Admin HSSE** | `admin.hsse@pertamina.demo` | `password123` | Master sarfas, checklist template, penjadwalan, monitoring kepatuhan. |
| **Inspektor Lapangan** | `inspektor.maos@pertamina.demo` | `password123` | Pengisian checklist mobile, unggah foto bukti, simpan draft, kirim inspeksi. |
| **Verifikator / Approver** | `verifikator.hsse@pertamina.demo` | `password123` | Review dan verifikasi hasil inspeksi, verifikasi penutupan temuan fisik. |

> [!NOTE]
> Fitur 1-Click Quick Demo Login dan endpoint `/api/v1/auth/demo-accounts` otomatis dinonaktifkan jika `DEMO_MODE=false` atau saat aplikasi berjalan pada lingkungan produksi (`APP_ENV=production` / `NODE_ENV=production`).

---

## 6. Daftar Endpoint REST API (`/api/v1`)

| Method | Endpoint | Fungsi | Hak Akses |
| :--- | :--- | :--- | :--- |
| `GET` | `/health` | Pemeriksaan kesehatan API server | Publik |
| `POST` | `/api/v1/auth/login` | Autentikasi kredensial pengguna & terbitkan JWT | Publik (Rate limited: 30/15m) |
| `POST` | `/api/v1/auth/logout` | Revokasi JWT token ke logout denylist | Terautentikasi |
| `GET` | `/api/v1/auth/demo-accounts` | Daftar akun demo (hanya saat mode demo aktif) | Publik (Gated demo mode) |
| `GET` | `/api/v1/dashboard/summary` | KPI operasional, tren inspeksi, temuan prioritas | Terautentikasi |
| `GET` | `/api/v1/sarfas` | Inventaris sarfas dengan filter kategori & lokasi | Terautentikasi |
| `GET` | `/api/v1/sarfas/:id` | Rincian aset, QR code tag, riwayat inspeksi | Terautentikasi |
| `POST` | `/api/v1/sarfas` | Pendaftaran sarfas baru | Super Admin, Admin HSSE |
| `PATCH` | `/api/v1/sarfas/:id` | Pembaruan data sarfas | Super Admin, Admin HSSE |
| `GET` | `/api/v1/checklist-templates` | Daftar master checklist template | Terautentikasi |
| `POST` | `/api/v1/checklist-templates` | Buat template checklist & item penilaian | Super Admin, Admin HSSE |
| `GET` | `/api/v1/inspections` | Daftar inspeksi tersaring | Terautentikasi |
| `POST` | `/api/v1/inspections/draft` | Inisiasi / simpan draft inspeksi | Super Admin, Admin HSSE, Inspektor |
| `PUT` | `/api/v1/inspections/:id` | Update draft inspeksi (anti-IDOR: hanya pemilik) | Super Admin, Admin HSSE, Pemilik Draft |
| `POST` | `/api/v1/inspections/:id/submit`| Submit inspeksi final & validasi kelengkapan fisik | Super Admin, Admin HSSE, Pemilik Draft |
| `POST` | `/api/v1/inspections/:id/review`| Peninjauan & persetujuan hasil inspeksi | Super Admin, Admin HSSE, Approver |
| `GET` | `/api/v1/findings` | Daftar temuan & matriks risiko | Terautentikasi |
| `POST` | `/api/v1/findings/:id/actions` | Tindakan korektif & unggah bukti setelah perbaikan | Terautentikasi (PIC / Inspektor) |
| `POST` | `/api/v1/findings/:id/verify` | Verifikasi penutupan temuan | Super Admin, Admin HSSE, Approver |
| `GET` | `/api/v1/inspection-schedules` | Kalender jadwal & deteksi overdue | Terautentikasi |
| `POST` | `/api/v1/inspection-schedules`| Penjadwalan inspeksi berkala baru | Super Admin, Admin HSSE |
| `GET` | `/api/v1/reports/inspections` | Agregasi data laporan, kepatuhan & paginasi | Terautentikasi |
| `GET` | `/api/v1/users` | Daftar pengguna & peranan | Super Admin, Admin HSSE |
| `POST` | `/api/v1/users` | Buat pengguna baru | Super Admin |
| `PATCH`| `/api/v1/users/:id` | Perbarui akun pengguna (proteksi akun Super Admin)| Super Admin |
| `GET` | `/api/v1/audit-logs` | Jejak audit kepatuhan & aktivitas sistem | Super Admin, Admin HSSE |

---

## 7. Catatan Pemecahan Masalah (Troubleshooting)

1. **Port 3001 Sudah Terpakai (`EADDRINUSE: address already in use :::3001`):**
   Jika proses Node sebelumnya masih berjalan di background pada Windows:
   ```powershell
   Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force
   ```
2. **Koneksi Database MySQL Gagal (`ECONNREFUSED 127.0.0.1:3306`):**
   - Pastikan service MySQL/MariaDB pada WAMPServer dalam status Running (Ikon WAMP hijau).
   - Pastikan port MySQL di `backend/.env` cocok dengan port WAMPServer (default: `3306`).
3. **API Tidak Merespons pada Frontend:**
   - Jalankan pemeriksaan endpoint kesehatan melalui browser atau terminal: `curl http://localhost:3001/health`.
   - Pastikan variabel `EXPRESS_API_URL=http://localhost:3001/api/v1` pada file `frontend/.env`.

---

## 8. Disclaimer Data Demonstrasi

> **Pernyataan Data Contoh:**  
> Seluruh data yang disajikan di dalam aplikasi ini (meliputi inventaris sarfas, nama personil, nomor telepon, catatan inspeksi, foto demonstrasi, dan nomor temuan) merupakan **data contoh (dummy/fixture)** yang dirancang semata-mata untuk kebutuhan pengujian fungsionalitas dan demonstrasi antarmuka mockup aplikasi digitalisasi HSSE. Data tersebut **bukan merupakan catatan resmi rahasia operasional PT Pertamina Patra Niaga**.
