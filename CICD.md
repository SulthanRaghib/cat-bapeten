# 🚀 Dokumentasi CI/CD — CAT BAPETEN

Dokumentasi lengkap proses setup **Continuous Integration & Continuous Deployment** menggunakan **GitHub Actions** + **SSH + Rsync** ke shared hosting (DirectAdmin).

---

## 📋 Ringkasan Arsitektur

```
Developer (Push ke master)
        │
        ▼
  GitHub Actions Runner (ubuntu-latest)
        │
        ├── 1. Checkout kode terbaru
        ├── 2. Install Composer dependencies (vendor/)
        ├── 3. Build frontend assets (npm run build → public/build/)
        └── 4. Upload semua file via Rsync (SSH)
                        │
                        ▼
              Server Hosting (DirectAdmin)
                        │
                        ├── 5. Hapus bootstrap cache lama
                        ├── 6. php artisan migrate --force
                        ├── 7. Rebuild config/route/view cache
                        ├── 8. filament:optimize
                        └── 9. storage:link
```

**Strategi vendor:**

> Composer **tidak dijalankan di server** untuk menghindari OOM (Out of Memory / Exit 137) pada shared hosting. Folder `vendor/` di-build di GitHub Runner lalu di-upload via Rsync.

---

## 🖥️ Informasi Server

> ⚠️ Detail server (host, user, path) disimpan sebagai **GitHub Secrets** — tidak di-hardcode di sini.

| Parameter  | GitHub Secret      | Keterangan                         |
| ---------- | ------------------ | ---------------------------------- |
| Host       | `SSH_HOST`         | Domain atau IP server              |
| User SSH   | `SSH_USER`         | Username SSH hosting               |
| Port SSH   | `SSH_PORT`         | Default: `22`                      |
| Path PHP   | _(di workflow)_    | Sesuaikan dengan versi PHP hosting |
| Target Dir | `TARGET_DIR`       | Absolute path ke `public_html`     |
| Panel      | _(tidak disimpan)_ | DirectAdmin / cPanel               |

---

## 🔑 Step 1 — Generate SSH Key Pair

Dilakukan **di komputer lokal** (Git Bash / terminal).

```bash
# Buat key pair ED25519
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/cat_bapeten_deploy
```

> Tekan Enter dua kali (tanpa passphrase) agar GitHub Actions bisa login otomatis.

Perintah ini menghasilkan dua file:

| File                            | Fungsi                                           |
| ------------------------------- | ------------------------------------------------ |
| `~/.ssh/cat_bapeten_deploy`     | **Private key** → disimpan sebagai GitHub Secret |
| `~/.ssh/cat_bapeten_deploy.pub` | **Public key** → dipasang di server              |

---

## 🖧 Step 2 — Pasang Public Key di Server

Tujuan: server perlu "mengenali" GitHub Actions sebagai pihak yang dipercaya.

### Opsi A — via `ssh-copy-id` (paling mudah)

```bash
ssh-copy-id -i ~/.ssh/cat_bapeten_deploy.pub -p 22 YOUR_USER@YOUR_HOST
```

### Opsi B — Manual (jika `ssh-copy-id` tidak tersedia)

```bash
cat ~/.ssh/cat_bapeten_deploy.pub | ssh YOUR_USER@YOUR_HOST \
  "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys"
```

### Opsi C — via DirectAdmin Panel

1. Login DirectAdmin → **SSH Keys** → **Add Key**
2. Paste isi file `cat_bapeten_deploy.pub`

### Verifikasi

Setelah public key terpasang, test koneksi tanpa password dari lokal:

```bash
ssh -i ~/.ssh/cat_bapeten_deploy YOUR_USER@YOUR_HOST "echo OK"
```

Output yang diharapkan:

```
OK
```

---

## 🔐 Step 3 — Setup GitHub Repository Secrets

Buka: **GitHub Repo → Settings → Secrets and variables → Actions → New repository secret**

Tambahkan 5 secrets berikut:

| Secret Name       | Nilai                                                          |
| ----------------- | -------------------------------------------------------------- |
| `SSH_HOST`        | Domain subdomain Anda (misal: `sub.domain.com`)                |
| `SSH_USER`        | Username SSH hosting Anda                                      |
| `SSH_PORT`        | `22` (atau port custom dari hosting)                           |
| `TARGET_DIR`      | Absolute path ke `public_html` (lihat di File Manager hosting) |
| `SSH_PRIVATE_KEY` | Isi seluruh file `~/.ssh/cat_bapeten_deploy` (lihat di bawah)  |

### Cara mendapatkan isi Private Key

```bash
cat ~/.ssh/cat_bapeten_deploy
```

Copy **seluruh output** termasuk header dan footer:

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXkAAAAA...
...
-----END OPENSSH PRIVATE KEY-----
```

> ⚠️ Jangan sampai ada spasi/baris kosong di awal atau akhir saat paste ke GitHub Secret.

---

## 📁 Step 4 — Setup `.env` di Server (Sekali Saja)

File `.env` **tidak pernah dikirim via Rsync** (di-exclude oleh workflow). Harus dibuat manual sekali di server:

```bash
# Login ke server (sesuaikan user dan host)
ssh YOUR_USER@YOUR_HOST

# Masuk ke direktori aplikasi (sesuaikan dengan TARGET_DIR di Secrets)
cd /home/YOUR_USER/domains/YOUR_SUBDOMAIN/public_html

# Buat .env dari template
cp .env.example .env
nano .env
```

Isi minimal yang wajib diubah:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://subdomain.domainanda.com

DB_HOST=localhost
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

Generate APP_KEY:

```bash
# Sesuaikan path PHP dengan versi di hosting Anda (cek dengan: which php)
/usr/local/phpXX/bin/php artisan key:generate
```

---

## 📂 Step 5 — Pastikan Permission `storage/` Benar di Server

```bash
chmod -R 775 storage bootstrap/cache
```

Jika ada masalah permission file upload:

```bash
chown -R YOUR_SSH_USER:YOUR_SSH_USER storage bootstrap/cache
```

---

## 🏗️ Step 6 — File Workflow GitHub Actions

File: [.github/workflows/deploy.yml](.github/workflows/deploy.yml)

### Penjelasan tiap step

| Step | Nama             | Fungsi                                                    |
| ---- | ---------------- | --------------------------------------------------------- |
| 1    | Checkout Code    | Ambil kode terbaru dari branch `master`                   |
| 2    | Setup PHP 8.2    | Siapkan PHP di GitHub Runner untuk Composer               |
| 3    | Install Composer | Build `vendor/` di Runner (bukan di server)               |
| 4    | Setup Node.js 20 | Siapkan Node untuk build frontend                         |
| 5    | Build Assets     | `npm install && npm run build` — hasilkan `public/build/` |
| 6    | Validate Secrets | Gagalkan workflow lebih awal jika ada secret kosong       |
| 7    | Configure SSH    | Tulis private key ke `~/.ssh/deploy_key` di Runner        |
| 8    | Rsync Deploy     | Upload semua file ke server via Rsync over SSH            |
| 9    | Remote Commands  | Jalankan migrasi + rebuild cache di server via SSH        |

### File & folder yang **di-exclude** dari Rsync

| Path                           | Alasan                                               |
| ------------------------------ | ---------------------------------------------------- |
| `.git/`                        | Version control, tidak perlu di server               |
| `.github/`                     | CI config, tidak perlu di server                     |
| `.env`                         | Konfigurasi sensitif, dikelola manual di server      |
| `.env.example`                 | Template, tidak perlu                                |
| `node_modules/`                | Tidak perlu, asset sudah di-build ke `public/build/` |
| `storage/framework/sessions/*` | Data session aktif, jangan dihapus                   |
| `storage/framework/cache/*`    | Cache runtime, jangan ditimpa                        |
| `storage/framework/views/*`    | Compiled views, akan diregenerate oleh `view:cache`  |
| `storage/logs/*`               | Log server, jangan ditimpa                           |
| `storage/app/public/*`         | File upload user, jangan dihapus                     |
| `public/storage`               | Symlink, dibuat ulang oleh `storage:link`            |

---

## ⚙️ Step 7 — Perintah Remote yang Dijalankan di Server

Setelah upload selesai, workflow SSH ke server dan menjalankan:

```bash
# 1. Bersihkan bootstrap cache lama (manual rm agar tidak error jika Artisan belum bisa jalan)
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/routes-v7.php

# 2. Jalankan migrasi database
$PHP_BIN artisan migrate --force

# 3. Bersihkan semua cache
$PHP_BIN artisan optimize:clear

# 4. Rebuild cache production
$PHP_BIN artisan config:cache
$PHP_BIN artisan event:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan filament:optimize

# 5. Pastikan symlink storage ada
$PHP_BIN artisan storage:link || true
```

> `|| true` pada `storage:link` agar tidak error jika symlink sudah ada dari deploy sebelumnya.

---

## 🚦 Step 8 — Trigger Deploy Pertama

```bash
git add .github/workflows/deploy.yml
git commit -m "ci: add github actions deploy workflow"
git push origin master
```

Pantau progress di: **GitHub Repo → Actions → workflow run terbaru**

---

## ✅ Checklist Setup

- [x] SSH key pair di-generate (`ed25519`)
- [x] Public key terpasang di `~/.ssh/authorized_keys` server
- [x] Test SSH tanpa password berhasil (`echo OK`)
- [x] 5 GitHub Secrets sudah diisi
- [x] `.env` sudah ada di server dengan konfigurasi production
- [x] `storage/` permission sudah `775`
- [x] PHP path di workflow sudah benar (sesuai output `which php` di server)
- [x] Push ke `master` → Actions berjalan → deploy berhasil

---

## 🔧 Troubleshooting

### ❌ `Permission denied (publickey)`

Public key belum terpasang di server, atau format key salah. Ulangi Step 2.

### ❌ `SSH_HOST is empty` / secret validation gagal

Secret belum diisi di GitHub. Ulangi Step 3.

### ❌ `rsync: connection unexpectedly closed`

Cek apakah `rsync` tersedia di server:

```bash
which rsync
```

Jika tidak ada, hubungi provider hosting.

### ❌ `php artisan migrate` gagal: `SQLSTATE[HY000]`

Cek konfigurasi database di `.env` server. Pastikan nama database, user, dan password sudah benar.

### ❌ `storage:link` error: `already exists`

Normal — `|| true` sudah menangani ini. Jika tetap error, hapus symlink lama:

```bash
rm -f public/storage
$PHP_BIN artisan storage:link
# (PHP_BIN = path dari `which php` di server, misal /usr/local/php83/bin/php)
```

### ❌ Deploy berhasil tapi halaman error 500

Jalankan manual di server:

```bash
cd /path/to/your/public_html
/usr/local/phpXX/bin/php artisan optimize:clear
tail -f storage/logs/laravel.log
```

---

## 🔄 Alur Deploy Berikutnya

Setelah setup selesai, setiap kali push ke `master`:

```
git add .
git commit -m "feat: deskripsi perubahan"
git push origin master
```

GitHub Actions otomatis:

1. Build `vendor/` + `public/build/`
2. Upload ke server via Rsync
3. Migrasi + rebuild cache
4. Selesai dalam ±3–5 menit

---

_Dokumentasi ini dibuat untuk project CAT BAPETEN — © 2026 Sulthan Raghib Fillah & Tahta Anugrah Ananda P_
