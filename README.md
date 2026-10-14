# Checklist Sarfas HSSE — PT Pertamina Patra Niaga (FT Maos)

Sistem Informasi Digitalisasi Inspeksi, Pemeriksaan, Pemantauan, dan Pelaporan Kondisi Sarana dan Fasilitas (Sarfas) HSSE di lingkungan **PT Pertamina Patra Niaga — Fuel Terminal Maos (Integrated Terminal Cilacap)**.

---

## 1. Arsitektur Teknologi & Pemisahan Tanggung Jawab

Aplikasi ini dibangun menggunakan arsitektur decoupled yang memisahkan layer presentasi (Frontend), logika bisnis & REST API (Backend), dan database relasional:

```
[ Pengguna / Browser ]
          │ (HTTP / Session Cookie)
          ▼
┌───────────────────────────────────────────────┐
│              FRONTEND (Laravel 12)            │
│   • Blade Templates   • Tailwind CSS (v4)     │
│   • Alpine.js         • Chart.js (v4)         │
│   • ExpressApiService (Server-to-Server HTTP) │
│   Port: http://localhost:8000                 │
└───────────────────────┬───────────────────────┘
                        │ (REST API JSON / Bearer JWT)
                        ▼
┌───────────────────────────────────────────────┐
│          BACKEND API (Node.js + Express)      │
│   • REST API (/api/v1) • JWT Auth Middleware  │
│   • Business Logic     • Inspection Engine    │
│   • Finding Workflow   • Audit Trail Logger   │
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
> - **Laravel** hanya bertindak sebagai antarmuka pengguna (UI/UX) dan session server-side.
> - **Express.js** merupakan **satu-satunya gerbang** yang memiliki hak akses query ke database MySQL dan mengeksekusi logika bisnis (validasi, perhitungan kepatuhan, transisi status temuan, dan logging audit).
> - Tidak ada duplikasi database connection di Laravel. Kredensial database tidak pernah terekspos ke frontend atau browser pengguna.

---

## 2. Struktur Proyek

```
Checklist_Sarfas_HSSE/
├── backend/                             # Express REST API & Database
│   ├── src/
│   │   ├── config/                      # Konfigurasi database & JWT
│   │   ├── controllers/                 # Controller API (Auth, Sarfas, Inspeksi, dll)
│   │   ├── database/
│   │   │   ├── knexfile.js              # Konfigurasi Knex untuk WAMPServer MySQL
│   │   │   ├── migrations/              # 14 Tabel relasional HSSE
│   │   │   └── seeds/                   # Data demonstrasi FT Maos
│   │   ├── middlewares/                 # Auth JWT, Role Check, Audit Logger, Error Handler
│   │   ├── routes/                      # Route definisi API v1 (/api/v1/...)
│   │   ├── server.js                    # Entry point Express API (Port 3001)
│   │   └── test-api.js                  # Automated API Test Suite
│   ├── .env.example
│   └── package.json
│
├── frontend/                            # Laravel 12 Presentation Layer
│   ├── app/
│   │   ├── Http/Controllers/           # Controller Blade yang memanggil ExpressApiService
│   │   ├── Http/Middleware/            # EnsureApiAuthenticated (Session Guard)
│   │   └── Services/ExpressApiService.php # HTTP Client ke Express REST API
│   ├── resources/
│   │   ├── css/app.css                  # Tailwind CSS dengan palet korporat Pertamina
│   │   ├── js/app.js                    # Inisialisasi Alpine.js & Chart.js
│   │   └── views/
│   │       ├── layouts/app.blade.php    # Sidebar responsif, Topbar, Notifikasi
│   │       ├── auth/login.blade.php     # Halaman login + 1-Click Role Switcher
│   │       ├── dashboard/index.blade.php# 6 Kartu KPI, Grafik Chart.js, Temuan Kritis
│   │       ├── sarfas/                  # Inventaris Sarfas & Detail
│   │       ├── master-checklist/        # Master Checklist Template
│   │       ├── inspections/             # Wizard, Mobile Form, Show & Signature
│   │       ├── findings/                # Monitoring Temuan, Foto Sebelum/Sesudah, Verifikasi
│   │       ├── schedules/               # Jadwal Inspeksi & Deteksi Overdue
│   │       ├── reports/                 # Filter Laporan, Ekspor CSV & Cetak Resmi PDF
│   │       ├── users/                   # Manajemen Pengguna & Otorisasi
│   │       └── audit-logs/              # Jejak Audit & Aktivitas Pengguna
│   ├── routes/web.php
│   ├── .env.example
│   └── package.json
└── README.md
```

---

## 3. Identitas Visual & Palet Warna

Sistem antarmuka terinspirasi dari identitas korporat Pertamina:
- **Pertamina Red (`#D71920`)**: Warna utama aksi penting, tombol primer, dan status kritis.
- **Corporate Blue (`#00529B`)**: Warna sekunder, informasi, navigasi pendukung, dan tombol netral.
- **Corporate Green (`#008B5A`)**: Warna status keberhasilan, kondisi Baik, dan kepatuhan.
- **Safety Orange (`#F59E0B`)**: Warna perhatian, peringatan, dan status Perlu Tindak Lanjut.
- **Critical Red (`#DC2626`)**: Warna bahaya temuan risiko tinggi/kritis.
- **Background Utama (`#F5F7FA`)**: Kanvas lembut dengan kontras tinggi terhadap teks gelap (`#1F2937`).

---

## 4. Panduan Instalasi & Menjalankan Aplikasi

### A. Prasyarat Sistem
1. **PHP**: v8.2 atau lebih baru dengan ekstensi `curl`, `mbstring`, `openssl`, `pdo`.
2. **Composer**: v2.x.
3. **Node.js**: v18.x atau v20.x LTS & **npm**.
4. **WAMPServer**: Pastikan service **MySQL / MariaDB** aktif (ikon WAMP berwarna hijau).

---

### B. Konfigurasi Database (WAMPServer)
1. Buka **phpMyAdmin** melalui browser (`http://localhost/phpmyadmin`) atau terminal MySQL.
2. Buat database baru bernama `checklist_sarfas_hsse`:
   ```sql
   CREATE DATABASE checklist_sarfas_hsse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Default port MySQL WAMPServer biasanya `3306` (atau `3307`/`3308` jika dikonfigurasi demikian).

---

### C. Menjalankan Backend (Node.js + Express)
1. Masuk ke direktori `backend`:
   ```bash
   cd backend
   ```
2. Pasang dependency:
   ```bash
   npm install
   ```
3. Siapkan file konfigurasi environment:
   ```bash
   cp .env.example .env
   ```
   *Sesuaikan `DB_PORT`, `DB_USER`, dan `DB_PASSWORD` dengan kredensial WAMPServer Anda jika berbeda (default user: `root`, password: kosong).*
4. Jalankan migrasi tabel database:
   ```bash
   npm run migrate
   ```
5. Masukkan data demonstrasi (seed data):
   ```bash
   npm run seed
   ```
6. Jalankan pengujian otomatis untuk memverifikasi seluruh endpoint API:
   ```bash
   npm test
   ```
7. Jalankan server Express API:
   ```bash
   npm run dev
   ```
   *Express API akan aktif di `http://localhost:3001`.*

---

### D. Menjalankan Frontend (Laravel + Blade + Tailwind)
1. Buka jendela terminal baru dan masuk ke direktori `frontend`:
   ```bash
   cd frontend
   ```
2. Pasang dependency PHP & Node:
   ```bash
   composer install
   npm install
   ```
3. Siapkan file konfigurasi `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Pastikan `EXPRESS_API_URL=http://localhost:3001/api/v1`.*
4. Build bundle aset Tailwind CSS & Alpine.js:
   ```bash
   npm run build
   ```
5. Jalankan web server Laravel:
   ```bash
   php artisan serve --port=8000
   ```
   *Buka browser di `http://localhost:8000`.*

---

## 5. Akun Demonstrasi (1-Click Demo Login)

Halaman login (`http://localhost:8000/login`) dilengkapi fitur **1-Click Quick Demo Login** untuk beralih role secara instan saat presentasi:

| Role | Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@pertamina.demo` | `password123` | Akses penuh, manajemen user, konfigurasi, audit log lengkap. |
| **Admin HSSE** | `admin.hsse@pertamina.demo` | `password123` | Kelola master sarfas, template checklist, monitoring jadwal & review. |
| **Inspektor Lapangan** | `inspektor.maos@pertamina.demo` | `password123` | Melaksanakan checklist lapangan, foto bukti, draft & submit inspeksi. |
| **Verifikator / Approver** | `verifikator.hsse@pertamina.demo` | `password123` | Verifikasi tindakan perbaikan temuan, persetujuan & sign-off hasil. |

---

## 6. Fitur & Modul Utama

### 1. Dashboard HSSE Eksekutif
- **6 Indikator Kunci (KPI Cards):** Total Sarfas Terdaftar, Inspeksi Bulan Ini, Sarfas Kondisi Baik, Temuan Terbuka, Temuan Kritis, dan Inspeksi Terlambat.
- **Grafik Tren Interaktif (Chart.js):** Tren riwayat inspeksi per bulan dan proporsi distribusi temuan risiko.
- **Panel Tindak Lanjut Mendesak:** Menampilkan temuan risiko Tinggi & Kritis yang mendekati tenggat waktu perbaikan.
- **Jejak Audit Real-Time:** Menampilkan aktivitas operator secara live.

### 2. Inventaris Sarfas (Sarana & Fasilitas)
- Pengelompokan sarfas: APAR, Hydrant, Eyewash & Emergency Shower, Spill Kit, Kotak P3K, Rambu K3, dll.
- Filter multi-kriteria: Kategori, Lokasi, Status Kondisi (Baik, Perlu Perhatian, Rusak Ringan, Rusak Berat).
- Detail spesifikasi lengkap, simulasi QR Code tag aset fisik, riwayat inspeksi, dan log temuan terkait.

### 3. Master Checklist Template
- Pembuatan dan pemeliharaan template checklist dinamis per kategori sarfas tanpa mengubah kode program.
- Penentuan bagian pemeriksaan (Section), item kriteria, pilihan status (Baik, Tidak Baik, N/A), dan penanda **Kritis**.

### 4. Formulir Checklist Lapangan (Mobile-Friendly)
- Desain dioptimalkan untuk perangkat smartphone saat inspeksi fisik di area operasional tangki atau filling shed.
- **Sticky Progress Bar** yang menghitung progres kelengkapan butir pemeriksaan (misal: "8 dari 10 selesai").
- Validasi cerdas: Jika jawaban dipilih **Tidak Baik**, sistem otomatis membuka kolom catatan temuan, unggah foto bukti, dan penetapan tingkat risiko.
- Fitur **Simpan Sebagai Draft** agar inspektor dapat melanjutkan pemeriksaan tanpa kehilangan data.
- **Digital Signature** & lembar verifikasi supervisor HSSE.

### 5. Temuan & Tindak Lanjut (Corrective Action)
- Matriks Risiko 4 Tingkat: **Rendah**, **Sedang**, **Tinggi**, dan **Kritis**.
- Dokumentasi komparasi foto: **Sebelum Perbaikan** vs **Setelah Perbaikan**.
- Alur verifikasi ketat: Temuan tidak dapat ditutup sepihak, melainkan wajib melalui peninjauan oleh Verifikator HSSE.

### 6. Jadwal Inspeksi
- Kalender agenda inspeksi berkala (Harian, Mingguan, Bulanan, Triwulanan).
- Deteksi otomatis status jadwal: **Terjadwal**, **Selesai**, dan **Terlambat (Overdue)**.

### 7. Laporan Rekapitulasi & Ekspor
- Filter laporan komprehensif berdasarkan rentang tanggal, kategori, lokasi, dan status kondisi.
- **Ekspor CSV / Spreadsheet** secara instan sesuai filter aktif.
- **Pratinjau Cetak Formal (Kop Surat Resmi):** Tampilan cetak standar A4 dokumen HSSE Pertamina FT Maos lengkap dengan 3 kolom tanda tangan (Inspektor, Supervisor HSSE, Fuel Terminal Manager).

### 8. Manajemen Pengguna & Otorisasi
- Manajemen akun pengguna berbasis peran (Role-Based Access Control).
- Aktivasi / penonaktifan akun secara aman.

### 9. Jejak Audit (Audit Logs)
- Pencatatan otomatis setiap aksi penting di database: login, pembuatan sarfas, submit inspeksi, update tindakan, verifikasi penutupan temuan, lengkap dengan stempel waktu, IP address, dan rincian payload data.

---

## 7. Endpoint REST API (`/api/v1`)

| Method | Endpoint | Deskripsi | Otorisasi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Autentikasi pengguna & penerbitan JWT | Publik |
| `POST` | `/api/v1/auth/quick-login` | 1-Click login akun demonstrasi | Publik |
| `GET` | `/api/v1/dashboard/summary` | Agregasi KPI, statistik grafik, dan temuan prioritas | Terautentikasi |
| `GET` | `/api/v1/sarfas` | Daftar sarfas dengan pencarian dan filter | Terautentikasi |
| `POST` | `/api/v1/sarfas` | Tambah data sarfas baru | Admin HSSE / Super Admin |
| `GET` | `/api/v1/sarfas/:id` | Detail spesifikasi dan riwayat sarfas | Terautentikasi |
| `PATCH` | `/api/v1/sarfas/:id` | Perbarui data sarfas | Admin HSSE / Super Admin |
| `GET` | `/api/v1/checklist-templates` | Daftar master checklist template | Terautentikasi |
| `POST` | `/api/v1/checklist-templates` | Buat template checklist baru beserta item | Admin HSSE / Super Admin |
| `GET` | `/api/v1/inspections` | Daftar riwayat dan antrean inspeksi | Terautentikasi |
| `POST` | `/api/v1/inspections` | Inisiasi draft inspeksi sarfas | Inspektor / Admin |
| `GET` | `/api/v1/inspections/:id` | Rincian hasil inspeksi dan lembar jawaban | Terautentikasi |
| `POST` | `/api/v1/inspections/:id/submit` | Submit inspeksi final & pembuatan temuan otomatis | Inspektor / Admin |
| `POST` | `/api/v1/inspections/:id/review` | Persetujuan / sign-off supervisor HSSE | Verifikator / Admin |
| `GET` | `/api/v1/findings` | Daftar temuan ketidaksesuaian sarfas | Terautentikasi |
| `GET` | `/api/v1/findings/:id` | Detail temuan, bukti foto & riwayat perbaikan | Terautentikasi |
| `POST` | `/api/v1/findings/:id/action` | Pembaruan tindakan korektif dan foto perbaikan | Inspektor / PIC |
| `POST` | `/api/v1/findings/:id/verify` | Verifikasi resmi penutupan temuan | Verifikator / Admin HSSE |
| `GET` | `/api/v1/inspection-schedules`| Daftar jadwal inspeksi mendatang & terlambat | Terautentikasi |
| `POST` | `/api/v1/inspection-schedules`| Buat jadwal inspeksi baru | Admin HSSE / Super Admin |
| `GET` | `/api/v1/reports/inspections` | Rekapitulasi laporan hasil inspeksi dan kepatuhan | Terautentikasi |
| `GET` | `/api/v1/users` | Daftar pengguna dan role | Super Admin / Admin HSSE |
| `POST` | `/api/v1/users` | Registrasi pengguna baru | Super Admin / Admin HSSE |
| `PATCH` | `/api/v1/users/:id` | Perbarui data akun atau status pengguna | Super Admin / Admin HSSE |
| `GET` | `/api/v1/audit-logs` | Daftar riwayat aktivitas & jejak audit sistem | Super Admin / Admin HSSE |

---

## 8. Disclaimer Data Demonstrasi

> **Pernyataan Data Contoh:**  
> Seluruh data yang disajikan di dalam aplikasi ini (meliputi daftar sarfas, nama personil, nomor telepon, catatan inspeksi, foto demonstrasi, dan nomor temuan) merupakan **data contoh (dummy/fixture)** yang dirancang semata-mata untuk kebutuhan pengujian fungsionalitas dan demonstrasi antarmuka mockup aplikasi digitalisasi HSSE. Data tersebut **bukan merupakan catatan resmi rahasia operasional PT Pertamina Patra Niaga**.
