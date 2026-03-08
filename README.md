<div align="center">

# 🖥️ CAT BAPETEN

### Sistem Computer Assisted Test — Badan Pengawas Tenaga Nuklir

<br>

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-v5.0-FFA500?style=for-the-badge&logo=filament&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

<br>

> Platform ujian berbasis komputer (CBT) yang dirancang untuk seleksi dan penilaian kepegawaian BAPETEN,
> dengan dukungan LaTeX, rich-text, penilaian bertingkat, dan monitoring ujian secara _real-time_.

</div>

---

## 📑 Daftar Isi

- [✨ Fitur Utama](#-fitur-utama)
- [⚙️ Teknologi](#️-teknologi)
- [📋 Prasyarat](#-prasyarat)
- [🚀 Instalasi](#-instalasi)
- [🗂️ Struktur Proyek](#️-struktur-proyek)
- [📦 Modul Sistem](#-modul-sistem)
- [🎓 Alur Ujian](#-alur-ujian)
- [👥 Peran & Akses](#-peran--akses)
- [📤 Ekspor Data](#-ekspor-data)
- [🏗️ Arsitektur](#️-arsitektur)
- [🔐 Akses Default](#-akses-default)
- [👨‍💻 Pengembang](#-pengembang)

---

## ✨ Fitur Utama

### 📝 Bank Soal

- Editor soal berbasis **Rich Text (TipTap)** dengan dukungan **LaTeX / MathJax** untuk persamaan matematika dan fisika nuklir
- Upload gambar langsung ke dalam soal via RichEditor
- Pengorganisasian soal berdasarkan **Unit**, **Sub-Unit**, dan **Indikator**
- Preview soal secara _live_ saat proses pembuatan/pengeditan
- Support 4 opsi jawaban (A–D) dengan penanda jawaban benar

### 📦 Manajemen Paket Ujian

- Buat paket ujian dengan penjadwalan waktu buka/tutup
- Konfigurasi durasi ujian, jumlah soal, dan jenis ujian
- Konfigurasi **NAB (Nilai Ambang Batas)** per paket secara dinamis
- Manajemen peserta per paket ujian
- Konfigurasi **seleksi bertingkat** (CBT + wawancara/tahap lanjutan)

### 📊 Dua Metode Penilaian

| Metode      | Kode            | Deskripsi                                                      |
| ----------- | --------------- | -------------------------------------------------------------- |
| Benar–Salah | `correct_wrong` | Skor berdasarkan jumlah jawaban benar (ujian teknis)           |
| Berbobot    | `weighted`      | Setiap opsi jawaban memiliki bobot skor tersendiri (Mansoskul) |

### 🛡️ Proctoring Real-Time

- Pendeteksian **tab switching** (pindah tab/jendela)
- Pendeteksian **window blur** (minimasi browser)
- Pendeteksian **copy-paste** (seleksi teks)
- Semua pelanggaran dicatat ke **log aktivitas ujian** secara permanen
- Tampilan peringatan langsung di layar peserta

### ⏱️ Pengalaman Ujian

- Timer hitung mundur dengan **auto-submit** saat waktu habis
- Navigasi soal non-linear (peserta dapat loncat-loncat soal)
- Penanda soal yang belum dijawab
- Pengacakan urutan soal (**shuffle**) saat sesi dimulai
- Optimistic UI dengan Alpine.js (jawaban tersimpan tanpa full reload)

### 📈 Pelaporan & Monitoring

- **Dashboard** dengan statistik real-time: total ujian, distribusi skor, tingkat kelulusan global
- **Monitoring Ujian** live: pantau peserta yang sedang mengerjakan ujian
- Halaman **Hasil Ujian** dengan detail skor, pelanggaran, dan status kelulusan
- Ekspor hasil ke **Excel**, **PDF**, dan **Word**
- Unduh **BAP (Berita Acara Pelaksanaan)** ujian resmi

---

## ⚙️ Teknologi

| Kategori         | Teknologi                        | Versi                     |
| ---------------- | -------------------------------- | ------------------------- |
| 🧩 Framework     | Laravel                          | 12.x                      |
| 🎛️ Admin Panel   | Filament                         | v5.0                      |
| ⚡ Reaktivitas   | Livewire                         | 3.x                       |
| 🖱️ Frontend JS   | Alpine.js                        | (bundled with Livewire 3) |
| 🎨 CSS Framework | Tailwind CSS                     | v4                        |
| ⚙️ Build Tool    | Vite                             | 7.x                       |
| 🐘 Bahasa        | PHP                              | ^8.2                      |
| 🗄️ Database      | MySQL                            | 8.0+                      |
| 📄 PDF Export    | barryvdh/laravel-dompdf          | ^3.1                      |
| 📝 Word Export   | phpoffice/phpword                | ^1.4                      |
| 📊 Excel Export  | pxlrbt/filament-excel            | ^3.4                      |
| 🔢 Matematika    | MathJax                          | CDN (v3)                  |
| 🖋️ Rich Text     | TipTap (via Filament RichEditor) | —                         |
| 🔑 Auth          | Laravel built-in + Filament Auth | —                         |

---

## 📋 Prasyarat

Pastikan environment Anda memenuhi persyaratan berikut sebelum instalasi:

- **PHP** >= 8.2 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`
- **Composer** >= 2.x
- **Node.js** >= 18.x dan **npm** >= 9.x
- **MySQL** >= 8.0 (atau MariaDB >= 10.6)
- **Web Server**: Apache / Nginx (atau PHP built-in server untuk development)
- Git

---

## 🚀 Instalasi

### Metode 1 — Setup Otomatis (Direkomendasikan)

```bash
# 1. Clone repositori
git clone <url-repositori> cat-bapeten
cd cat-bapeten

# 2. Salin file environment dan sesuaikan konfigurasi database
cp .env.example .env
# Edit file .env: isi DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Jalankan setup otomatis (install, migrasi, build)
composer setup
```

Perintah `composer setup` akan otomatis menjalankan:

- `composer install`
- Generate `APP_KEY`
- `php artisan migrate`
- `npm install`
- `npm run build`

### Metode 2 — Setup Manual

```bash
# 1. Clone repositori
git clone <url-repositori> cat-bapeten
cd cat-bapeten

# 2. Install dependensi PHP
composer install

# 3. Salin dan konfigurasi environment
cp .env.example .env
# Edit .env sesuai konfigurasi lokal Anda

# 4. Generate application key
php artisan key:generate

# 5. Jalankan migrasi dan seeder database
php artisan migrate --seed

# 6. Buat symlink storage publik
php artisan storage:link

# 7. Install dependensi frontend
npm install

# 8. Build aset frontend
npm run build
```

### Menjalankan Server Development

```bash
# Menjalankan semua proses sekaligus (server + queue + log viewer + vite hot-reload)
composer dev
```

Perintah ini menjalankan secara paralel:

- `php artisan serve` — web server
- `php artisan queue:listen` — queue worker
- `php artisan pail` — log viewer
- `npm run dev` — Vite hot-reload

---

## 🗂️ Struktur Proyek

```
cat-bapeten/
├── app/
│   ├── DTOs/                          # Data Transfer Objects (immutable input contracts)
│   │   ├── ExamPackage/               # DTOs untuk paket ujian
│   │   ├── ExamSession/               # DTOs untuk sesi ujian
│   │   └── Question/                  # DTOs untuk soal (+ QuestionFormDataMapper)
│   ├── Filament/
│   │   ├── Actions/                   # Reusable Filament actions
│   │   │   └── ValidateCorrectAnswerAction.php
│   │   ├── Auth/
│   │   │   └── CustomLogin.php        # Halaman login kustom
│   │   ├── Resources/
│   │   │   ├── ExamMonitors/          # Monitoring ujian live
│   │   │   ├── ExamPackages/          # CRUD paket ujian + RelationManagers
│   │   │   ├── ExamParticipants/      # Manajemen peserta
│   │   │   ├── ExamResults/           # Laporan hasil ujian
│   │   │   ├── ExamTypes/             # Jenis ujian (Teknis / Mansoskul)
│   │   │   ├── Questions/             # CRUD bank soal
│   │   │   ├── QuestionSubUnits/      # Sub-unit soal
│   │   │   ├── QuestionUnits/         # Unit soal + indikator
│   │   │   ├── SelectionStageTypes/   # Tipe tahap seleksi
│   │   │   └── Users/                 # Manajemen pengguna
│   │   └── Widgets/                   # Dashboard widgets
│   ├── Http/
│   │   ├── Controllers/               # QuestionImageUploadController, BapController
│   │   └── Middleware/
│   │       └── BlockParticipantsFromAdmin.php
│   ├── Livewire/
│   │   └── Exam/
│   │       └── ExamPage.php           # Halaman ujian interaktif (Livewire component)
│   ├── Models/                        # Eloquent models
│   ├── Providers/
│   │   └── Filament/
│   │       └── AdminPanelProvider.php # Konfigurasi panel admin
│   └── Services/                      # Business logic layer
│       ├── ExamPackageService.php
│       ├── ExamResultsPdfExportService.php
│       ├── ExamSessionService.php
│       ├── NabSyncService.php
│       ├── QuestionPdfExportService.php
│       └── QuestionService.php
├── database/
│   ├── migrations/                    # 27 migrasi database
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php             # Admin + 10 user acak
│       └── QuestionSeeder.php
├── resources/
│   ├── css/                           # Tailwind CSS v4
│   ├── js/                            # Alpine.js bootstrap
│   └── views/
│       ├── filament/
│       │   └── components/
│       │       └── question-preview.blade.php  # Live preview soal
│       └── livewire/
│           └── exam/                  # Tampilan halaman ujian
├── routes/
│   ├── web.php                        # Rute aplikasi
│   └── console.php
└── vite.config.js                     # Konfigurasi Vite + Tailwind v4
```

---

## 📦 Modul Sistem

### 🎛️ Panel Admin (`/admin`)

Panel admin dibangun dengan **Filament v5** dengan tema warna **Amber** dan font **Poppins**.

#### Grup Navigasi: Manajemen Ujian

| Modul              | Deskripsi                                                                                                      |
| ------------------ | -------------------------------------------------------------------------------------------------------------- |
| 📚 **Bank Soal**   | CRUD soal dengan rich editor, LaTeX, upload gambar, preview live, dan kategorisasi per unit/sub-unit/indikator |
| 📦 **Paket Ujian** | CRUD paket ujian lengkap dengan manajemen soal, peserta, dan konfigurasi NAB via RelationManager               |

#### Grup Navigasi: Laporan & Hasil

| Modul                   | Deskripsi                                                                                   |
| ----------------------- | ------------------------------------------------------------------------------------------- |
| 📊 **Hasil Ujian**      | Laporan lengkap sesi yang telah selesai: skor, kelulusan, breakdown jawaban, ekspor dokumen |
| 👁️ **Monitoring Ujian** | Pantau peserta yang sedang mengerjakan ujian secara real-time (status `ongoing`/`paused`)   |

#### Modul Konfigurasi

| Modul                     | Deskripsi                                             |
| ------------------------- | ----------------------------------------------------- |
| 👤 **Pengguna**           | Manajemen akun admin dan peserta                      |
| 🏷️ **Jenis Ujian**        | Konfigurasi tipe ujian (Teknis CBT, Mansoskul, dll.)  |
| 🗂️ **Unit Soal**          | Hierarki organisasi soal: Unit → Sub-Unit → Indikator |
| 🔢 **Sub-Unit Soal**      | Manajemen sub-unit per unit soal                      |
| 📌 **Tipe Tahap Seleksi** | Konfigurasi tahap seleksi lanjutan (wawancara, dll.)  |

### 📊 Dashboard Widgets

| Widget                        | Fungsi                                                          |
| ----------------------------- | --------------------------------------------------------------- |
| 📈 **ExamStatsOverview**      | Statistik ringkasan: total ujian, peserta aktif, rata-rata skor |
| 🌐 **GlobalPassRateChart**    | Grafik tingkat kelulusan global per paket ujian                 |
| 📅 **ScheduledExamWidget**    | Daftar ujian yang akan datang / sedang berjalan                 |
| 📉 **ScoreDistributionChart** | Grafik distribusi skor peserta                                  |
| ❓ **QuestionStatsOverview**  | Statistik bank soal: total soal per unit/jenis                  |

---

## 🎓 Alur Ujian

Peserta mengakses ujian melalui rute **`/ujian`** (terpisah dari panel admin):

```
[Login] → [/ujian]
    │
    ▼
[1. Verifikasi Token]          ← Validasi token peserta + paket aktif
    │
    ▼
[2. Halaman Tata Tertib]       ← Konfirmasi persetujuan sebelum mulai
    │
    ▼
[3. Sesi Ujian Aktif]          ← Timer aktif, soal diacak, jawaban tersimpan optimistis
    │  ├── Navigasi soal bebas (non-linear)
    │  ├── Penanda soal belum dijawab
    │  ├── Proctoring aktif (tab switch, copy-paste, window blur)
    │  └── Auto-submit saat waktu habis
    │
    ▼
[4. Halaman Hasil]             ← Skor, status lulus/tidak, detail pelanggaran
    │
    └─ (jika paket bertingkat) → Status: "Menunggu Wawancara"
```

---

## 👥 Peran & Akses

| Peran             | Kode    | Akses                                                                       |
| ----------------- | ------- | --------------------------------------------------------------------------- |
| **Administrator** | `admin` | Akses penuh ke panel admin (`/admin`): CRUD semua modul, monitoring, ekspor |
| **Peserta**       | `user`  | Hanya akses ke halaman ujian (`/ujian`); **diblokir** dari panel admin      |

> Middleware `BlockParticipantsFromAdmin` secara otomatis mengalihkan pengguna dengan role `user` yang mencoba mengakses `/admin`.

---

## 📤 Ekspor Data

Sistem mendukung berbagai format ekspor dokumen dari modul **Hasil Ujian**:

| Format                 | Library                        | Keterangan                                                             |
| ---------------------- | ------------------------------ | ---------------------------------------------------------------------- |
| 📊 **Excel** (.xlsx)   | `pxlrbt/filament-excel` ^3.4   | Tabel hasil ujian seluruh peserta                                      |
| 📄 **PDF**             | `barryvdh/laravel-dompdf` ^3.1 | Laporan hasil ujian terformat                                          |
| 📝 **Word** (.docx)    | `phpoffice/phpword` ^1.4       | Dokumen formal cetak                                                   |
| 📋 **BAP** (PDF)       | `barryvdh/laravel-dompdf` ^3.1 | Berita Acara Pelaksanaan ujian resmi (unduh via `/admin/bap/download`) |
| 📚 **Bank Soal** (PDF) | `barryvdh/laravel-dompdf` ^3.1 | Cetak kumpulan soal dengan kunci jawaban                               |

---

## 🏗️ Arsitektur

Sistem dibangun dengan prinsip **Clean Architecture** yang memisahkan concern secara tegas:

```
Filament Pages / Livewire Components
        │  (delegates to)
        ▼
    Services Layer              ← Seluruh business logic
  (ExamSessionService,
   ExamPackageService,
   QuestionService, dll.)
        │  (uses)
        ▼
      DTOs                      ← Immutable input contracts (readonly classes)
  (CreateQuestionDTO,
   UpdateQuestionDTO,
   ExamSessionStartDTO, dll.)
        │  (persisted by)
        ▼
  Eloquent Models               ← Data access layer
```

**Prinsip yang diterapkan:**

- `declare(strict_types=1)` di semua file PHP
- **DTOs readonly** — validasi dan transformasi data di satu titik
- **Services** memiliki seluruh business logic (bukan di Controller/Page)
- **DB transactions** pada semua operasi multi-tabel
- **QuestionFormDataMapper** — satu titik ekstraksi form data (DRY)
- **ValidateCorrectAnswerAction** — reusable action (tidak duplikat)

---

## 🔐 Akses Default

Setelah menjalankan `php artisan migrate --seed` atau `composer setup`:

| Field                | Nilai                         |
| -------------------- | ----------------------------- |
| 🌐 URL Admin Panel   | `http://localhost:8000/admin` |
| 🌐 URL Halaman Ujian | `http://localhost:8000/ujian` |
| 📧 Email Admin       | `admin@bapeten.com`           |
| 🔑 Password          | `password`                    |
| 🪪 NIP Admin         | `198001012024011001`          |

> ⚠️ **Penting:** Segera ganti password default setelah instalasi di lingkungan produksi.

---

## 👨‍💻 Pengembang

<div align="center">

Dikembangkan dengan ❤️ oleh:

**Sulthan Raghib Fillah** &nbsp;&amp;&nbsp; **Tahta Anugrah Ananda P**

<br>

© 2026 Sulthan Raghib Fillah & Tahta Anugrah Ananda P. All rights reserved.

</div>
