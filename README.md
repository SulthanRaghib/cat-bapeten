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
![Security](https://img.shields.io/badge/Security-Zero--Trust-DC2626?style=for-the-badge&logo=shield&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)

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
- [🔒 Keamanan & Zero-Trust](#-keamanan--zero-trust)
- [📱 Progressive Web App (PWA)](#-progressive-web-app-pwa)
- [🌐 Internasionalisasi (i18n)](#-internasionalisasi-i18n)
- [🎨 Tema Warna Per-User](#-tema-warna-per-user)
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

### 📱 Progressive Web App (PWA)

- **Dapat diinstall** di perangkat desktop & mobile layaknya aplikasi native — tanpa App Store atau Play Store
- **Bekerja offline** dengan halaman fallback branded saat koneksi terputus
- **Auto-update** service worker dengan konfirmasi pengguna — tidak pernah cache basi
- **Ikon maskable** 8 ukuran (72–512px) dengan amber branding BAPETEN untuk home screen Android & iOS
- **Strategi cache cerdas** via Workbox: aset statis `CacheFirst` (30 hari), halaman admin `NetworkFirst`, Livewire/API `NetworkOnly` (tidak pernah dicache untuk keamanan real-time)
- **Theme color amber** (`#d97706`) sesuai identitas visual sistem CAT BAPETEN

### 🌐 Internasionalisasi (i18n)

- Panel admin tersedia dalam **2 bahasa**: Bahasa Indonesia 🇮🇩 dan English 🇺🇸
- Bahasa disimpan di **session** — tidak perlu login ulang saat berganti bahasa
- **Language switcher** muncul di top bar panel admin, berupa dropdown dengan bendera negara
- Semua label UI (navigasi, kolom tabel, tombol aksi, judul halaman, notifikasi) sudah diterjemahkan via `__()`
- Default locale: `id` (Bahasa Indonesia)

### 🎨 Tema Warna Per-User

- Setiap admin dapat memilih **warna tema pribadinya** — tersimpan ke database per akun
- **10 pilihan warna**: Kuning (Amber), Oranye, Merah, Pink, Ungu, Biru Tua, Biru Langit, Biru Muda, Tosca, Hijau Limau
- Ganti tema via halaman **Pengaturan Tampilan** (`/admin/appearance-settings`) di grup navigasi Pengaturan
- Perubahan diterapkan **instan** via Livewire + `FilamentColor::register()` — tanpa reload halaman
- Warna default: **Amber** (untuk akun baru / yang belum memilih)

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
| 📱 PWA           | vite-plugin-pwa + Workbox        | v1.2.0                    |

---

## 📋 Prasyarat

Pastikan environment Anda memenuhi persyaratan berikut sebelum instalasi:

- **PHP** >= 8.2 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`
    > Ekstensi `gd` diperlukan untuk generate ikon PWA (`public/pwa/generate-icons.php`)
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
- `npm run build` ← _Sekaligus menghasilkan Service Worker & manifest PWA_

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

# 8. Build aset frontend (juga menghasilkan sw.js, workbox, dan manifest PWA)
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
│   ├── js/                            # Alpine.js bootstrap + PWA service worker registration
│   └── views/
│       ├── filament/
│       │   └── components/
│       │       └── question-preview.blade.php  # Live preview soal
│       ├── livewire/
│       │   └── exam/                  # Tampilan halaman ujian
│       └── pwa/
│           └── offline.blade.php      # Halaman fallback saat offline (amber-branded)
├── routes/
│   ├── web.php                        # Rute aplikasi (termasuk /offline untuk PWA)
│   └── console.php
├── public/
│   ├── sw.js                          # Service Worker (auto-generated oleh npm run build)
│   ├── workbox-*.js                   # Workbox runtime (auto-generated oleh npm run build)
│   ├── build/
│   │   └── manifest.webmanifest       # PWA Web App Manifest (auto-generated)
│   └── pwa/
│       ├── icon-{72,96,128,...}.png   # Ikon PWA 8 ukuran (72–512px, amber amber BAPETEN)
│       └── generate-icons.php         # Generator ikon — jalankan sekali via PHP CLI
└── vite.config.js                     # Vite + Tailwind v4 + vite-plugin-pwa (generateSW)
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

### ⚙️ Pengaturan Pengguna

#### 🌐 Language Switcher (Ganti Bahasa)

Tersedia di **top bar** panel admin (pojok kanan atas, sebelum menu pengguna):

| Elemen      | Detail                                                           |
| ----------- | ---------------------------------------------------------------- |
| Lokasi      | `panels::user-menu.before` render hook                           |
| Pilihan     | 🇮🇩 Indonesia · 🇺🇸 English                                        |
| Mekanisme   | Route `/lang/{locale}` → set `session('locale')` → redirect back |
| Persistence | Session (tidak perlu login ulang)                                |
| Default     | `id` (Bahasa Indonesia)                                          |

#### 🎨 Color Theme Switcher (Ganti Tema Warna)

Tersedia di halaman **Pengaturan Tampilan** (`/admin/appearance-settings`):

| Warna       | Label          | Hex       |
| ----------- | -------------- | --------- |
| 🟡 `yellow` | Kuning (Amber) | `#f59e0b` |
| 🟠 `orange` | Oranye         | `#f97316` |
| 🔴 `red`    | Merah          | `#f43f5e` |
| 🩷 `pink`   | Pink           | `#ec4899` |
| 🟣 `purple` | Ungu           | `#a855f7` |
| 🔵 `indigo` | Biru Tua       | `#6366f1` |
| 🩵 `sky`    | Biru Langit    | `#0ea5e9` |
| 🔵 `cyan`   | Biru Muda      | `#06b6d4` |
| 🟢 `teal`   | Tosca          | `#14b8a6` |
| 🟢 `lime`   | Hijau Limau    | `#84cc16` |

Pilihan disimpan ke kolom `theme_color` di tabel `users`. Diterapkan via `FilamentColor::register()` saat event `ServingFilament` — berlaku instan setelah ganti dan simpan.

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

## 🔒 Keamanan & Zero-Trust

Sistem ini dirancang dengan prinsip **Zero-Trust Architecture** — tidak ada input dari client yang dipercaya tanpa verifikasi ulang di sisi server.

### Lapisan Perlindungan

#### 1. 🚫 Data Exposure Prevention (`ExamPage` — `initializeClientData`)

Field sensitif **distrip sebelum dikirim ke frontend**. `questionsJson` yang diterima browser hanya berisi teks soal dan teks pilihan — **tanpa** `is_correct`, `score`, atau `scoring_config`.

```php
// ❌ SEBELUM — is_correct bocor ke DevTools
'options' => $q->options,

// ✅ SESUDAH — hanya teks yang dikirim ke client
'options' => array_map(static fn ($opt) => [
    'answer_text' => $opt['answer_text'] ?? '',
    'is_active'   => $opt['is_active'] ?? true,
], $options),
```

#### 2. 🔐 State Tampering Prevention (`#[Locked]` Properties)

Semua properti Livewire yang bersifat **server-only** dikunci dengan `#[Locked]`. Penyerang tidak dapat memanipulasinya via `$wire.property = nilai` di DevTools.

| Properti           | Risiko Jika Tidak Dikunci                                |
| ------------------ | -------------------------------------------------------- |
| `$examSessionId`   | Bisa ditarget ke sesi milik peserta lain                 |
| `$endTime`         | Bisa diset ke tahun 2099 → waktu ujian tidak habis       |
| `$durationMinutes` | Bisa diperbesar → perpanjang waktu                       |
| `$questionIds`     | Bisa dimanipulasi → merusak IDOR guard                   |
| `$step`            | Bisa di-skip langsung ke `result` tanpa mengerjakan soal |
| `$resultStats`     | Bisa dipalsukan untuk tampilkan nilai berbeda            |

#### 3. 🛡️ IDOR Prevention (`saveAnswerClient`, `toggleDoubtfulClient`)

Setiap `questionId` yang datang dari browser **divalidasi** bahwa soal tersebut memang berada di daftar soal sesi peserta yang bersangkutan.

```php
// Cegah penyerang kirim jawaban untuk soal dari paket lain
if (! in_array($questionId, $this->questionIds, true)) {
    return; // Ditolak diam-diam
}
```

#### 4. ⚡ IDOR + Race Condition Prevention (`ExamSessionService::saveAnswer`)

Verifikasi ganda di layer Service menggunakan **`lockForUpdate()`** agar tidak ada dua request paralel yang memanipulasi sesi secara bersamaan.

```php
$session = ExamSession::where('id', $dto->examSessionId)
    ->lockForUpdate()      // ← Cegah race condition
    ->firstOrFail();

// Pastikan questionId ada di answers_meta session ini
if (! in_array($dto->questionId, $session->answers_meta ?? [], true)) {
    throw new \RuntimeException('IDOR attempt rejected.');
}
```

#### 5. 📝 Input Whitelist Validation

Dua whitelist konstan mencegah injection data sembarang:

```php
// Hanya nilai opsi ini yang valid
private const VALID_ANSWER_OPTIONS = ['A', 'B', 'C', 'D', 'E', ''];

// Hanya aksi proctoring ini yang diizinkan masuk log
private const ALLOWED_PROCTORING_ACTIONS = [
    'tab_switch', 'window_blur', 'copy_attempt', 'paste_attempt', 'right_click',
];
```

Pesan log juga sepenuhnya dibuat di sisi server dari `$messageMap` — **pesan bebas dari client diabaikan**.

#### 6. 🔄 ACID Compliance

Seluruh operasi mutasi data (save answer, start session, finish session) dibungkus dalam `DB::transaction()` sehingga partial write tidak bisa terjadi meski ada network failure.

---

## 📱 Progressive Web App (PWA)

CAT BAPETEN dilengkapi dengan dukungan **Progressive Web App** penuh menggunakan `vite-plugin-pwa` v1.2.0 + Google Workbox, memungkinkan sistem diinstall dan digunakan layaknya aplikasi native di perangkat manapun.

### Arsitektur PWA

| Komponen         | Path                          | Keterangan                                       |
| ---------------- | ----------------------------- | ------------------------------------------------ |
| Service Worker   | `/sw.js`                      | Auto-generated oleh Workbox saat `npm run build` |
| Workbox Runtime  | `/workbox-*.js`               | Modul Workbox (auto-generated)                   |
| Web App Manifest | `/build/manifest.webmanifest` | Metadata instalasi PWA                           |
| Offline Fallback | `/offline`                    | Halaman branded saat tidak ada koneksi           |
| Ikon PWA         | `/pwa/icon-*.png`             | 8 ukuran: 72, 96, 128, 144, 152, 192, 384, 512px |

### Strategi Caching

| Pola URL                      | Strategi       | Cache Name           | TTL           |
| ----------------------------- | -------------- | -------------------- | ------------- |
| `/build/*.js`, `/build/*.css` | `CacheFirst`   | `cat-bapeten-assets` | 30 hari       |
| `/fonts/`, `/css/filament/`   | `CacheFirst`   | `cat-bapeten-fonts`  | 60 hari       |
| `/pwa/*.png`                  | `CacheFirst`   | `cat-bapeten-icons`  | 365 hari      |
| `/admin/**` (halaman)         | `NetworkFirst` | `cat-bapeten-pages`  | 24 jam        |
| Livewire calls, `/api/*`      | `NetworkOnly`  | —                    | Tidak dicache |

> **Kenapa Livewire `NetworkOnly`?** Livewire melakukan AJAX calls yang membawa state sesi aktif. Meng-cache respons ini bisa menyebabkan jawaban ujian tidak tersimpan. Keamanan real-time selalu diutamakan.

### Cara Install Aplikasi

#### 💻 Desktop (Chrome / Edge)

1. Buka `https://<alamat-sistem>/admin` di Chrome atau Edge
2. Klik ikon **Install** (⊕) di sebelah kanan address bar
3. Klik **Install** pada dialog konfirmasi
4. Aplikasi terbuka di jendela sendiri dan muncul di Start Menu / Desktop

#### 🤖 Android

1. Buka `https://<alamat-sistem>/admin` di Chrome Mobile
2. Tap menu **⋮** (tiga titik) di pojok kanan atas
3. Pilih **"Tambahkan ke layar utama"** atau **"Install app"**
4. Tap **Tambah** — ikon BAPETEN muncul di home screen

#### 🍎 iOS (iPhone / iPad)

1. Buka `https://<alamat-sistem>/admin` di **Safari** (wajib Safari untuk iOS PWA)
2. Tap ikon **Share** (kotak dengan panah ke atas)
3. Scroll dan pilih **"Add to Home Screen"**
4. Tap **Add** — aplikasi terinstall dengan ikon BAPETEN

### Generate Ulang Ikon PWA

Jika perlu mengganti ikon (misalnya logo berubah), jalankan:

```bash
php public/pwa/generate-icons.php
```

Script ini menggunakan ekstensi PHP **GD** untuk menghasilkan semua 8 ukuran ikon secara otomatis dengan latar amber (`#d97706`) dan motif atom BAPETEN.

### Update Service Worker

Setiap kali ada perubahan aset frontend (JS/CSS), build ulang untuk memperbarui Service Worker:

```bash
npm run build
```

Pengguna yang sudah menginstall aplikasi akan melihat notifikasi **"Versi baru tersedia"** dan dapat memilih untuk memperbarui aplikasi.

---

## 🌐 Internasionalisasi (i18n)

Panel admin CAT BAPETEN tersedia dalam **2 bahasa** dan dapat berganti secara dinamis tanpa reload penuh.

### Cara Berganti Bahasa

Klik dropdown **bendera negara** di pojok kanan atas panel admin (sebelum foto profil), lalu pilih bahasa yang diinginkan.

### Teknis

| Komponen          | Detail                                                                                    |
| ----------------- | ----------------------------------------------------------------------------------------- |
| Middleware        | `SetLocale` — membaca `session('locale')`, apply ke `App::setLocale()`                    |
| Route             | `GET /lang/{locale}` — validasi whitelist `['id', 'en']`, set session, redirect back      |
| UI                | `resources/views/filament/locale-switcher.blade.php` — Alpine.js dropdown dengan SVG flag |
| Inject            | `panels::user-menu.before` render hook di `AdminPanelProvider`                            |
| Lokasi terjemahan | `lang/id.json` (ID) dan `lang/en.json` / Laravel built-in (EN)                            |
| Default           | `id` — dikonfigurasi di `config/app.php` dan `.env` `APP_LOCALE`                          |

### Bahasa yang Didukung

| Kode | Bahasa           | Flag |
| ---- | ---------------- | ---- |
| `id` | Bahasa Indonesia | 🇮🇩   |
| `en` | English          | 🇺🇸   |

---

## 🎨 Tema Warna Per-User

Setiap admin dapat mempersonalisasi warna tema panel sesuai preferensi. Warna disimpan per akun ke database dan diterapkan otomatis saat login.

### Cara Mengganti Tema

Buka **Pengaturan Tampilan** dari menu navigasi kiri (grup "Pengaturan") → `/admin/appearance-settings`, lalu klik swatch warna yang diinginkan.

### Pilihan Warna

| Warna    | Label          | Hex       | Preview |
| -------- | -------------- | --------- | ------- |
| `yellow` | Kuning (Amber) | `#f59e0b` | 🟡      |
| `orange` | Oranye         | `#f97316` | 🟠      |
| `red`    | Merah          | `#f43f5e` | 🔴      |
| `pink`   | Pink           | `#ec4899` | 🩷      |
| `purple` | Ungu           | `#a855f7` | 🟣      |
| `indigo` | Biru Tua       | `#6366f1` | 🔵      |
| `sky`    | Biru Langit    | `#0ea5e9` | 🩵      |
| `cyan`   | Biru Muda      | `#06b6d4` | 🔵      |
| `teal`   | Tosca          | `#14b8a6` | 🟢      |
| `lime`   | Hijau Limau    | `#84cc16` | 🟢      |

### Teknis

| Komponen      | Detail                                                                                |
| ------------- | ------------------------------------------------------------------------------------- |
| Kolom DB      | `users.theme_color` — `string`, nullable, default `'yellow'`                          |
| Livewire      | `App\Livewire\ColorThemeSwitcher` — `setColor()` update DB + dispatch `theme-changed` |
| Filament Page | `App\Filament\Pages\AppearanceSettings` — view dengan swatch picker                   |
| Apply warna   | `FilamentColor::register()` dipanggil saat event `ServingFilament`                    |
| Scope         | Per-user — setiap akun memiliki warna sendiri, tidak mempengaruhi user lain           |

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
