# 🕌 QUICKSTART — Keuangan Mushola

## Instalasi dalam 5 Menit

### Prasyarat
- PHP 8.2+
- Composer
- MySQL 5.7+ / MariaDB 10.3+
- Node.js 18+ (opsional, untuk build asset)

---

## Langkah 1 — Ekstrak & Install Dependencies

```bash
# Ekstrak ZIP ke folder tujuan
unzip mushola-finance.zip
cd mushola-finance

# Install PHP dependencies
composer install --no-dev --optimize-autoloader
```

---

## Langkah 2 — Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`, sesuaikan bagian ini:

```env
APP_NAME="Keuangan Mushola Al-Ikhlas"
APP_URL=http://localhost:8000

DB_DATABASE=mushola_keuangan
DB_USERNAME=root
DB_PASSWORD=your_password

GOOGLE_CLIENT_ID=xxx
GOOGLE_CLIENT_SECRET=xxx
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Langkah 3 — Setup Google OAuth

1. Buka **https://console.cloud.google.com/**
2. Buat project baru (misal: "Mushola Keuangan")
3. Aktifkan **Google+ API** atau **People API**
4. Buka **APIs & Services → Credentials**
5. Klik **Create Credentials → OAuth 2.0 Client ID**
6. Pilih **Web application**
7. Tambahkan di **Authorized redirect URIs**:
   ```
   http://localhost:8000/auth/google/callback
   ```
   (Ganti dengan domain asli saat production)
8. Salin **Client ID** dan **Client Secret** ke `.env`

---

## Langkah 4 — Database & Storage

```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE mushola_keuangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi dan data awal
php artisan migrate --seed

# Buat storage link (untuk upload foto kegiatan)
php artisan storage:link
```

---

## Langkah 5 — Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser: **http://localhost:8000**

---

## Langkah 6 — Set Admin Pertama

Setelah login dengan Google untuk pertama kali:

```bash
php artisan mushola:set-admin email@anda.com
```

Sekarang Anda bisa mengatur hak akses pengguna lain melalui menu **Admin → Hak Akses**.

---

## Opsional — Isi Data Contoh

```bash
# Isi data transaksi contoh
php artisan db:seed --class=DemoSeeder

# Isi data kegiatan contoh
php artisan db:seed --class=KegiatanSeeder
```

---

## Perintah Artisan Khusus

```bash
# Set admin berdasarkan email
php artisan mushola:set-admin email@domain.com

# Hitung ulang saldo semua kategori
php artisan mushola:recalculate
```

---

## Deployment Production

```bash
# Optimasi untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set APP_DEBUG=false di .env
# Set APP_ENV=production di .env
```

Untuk server Nginx, arahkan document root ke folder `/public`.

---

## Struktur Menu Aplikasi

| Menu | Akses | Fungsi |
|------|-------|--------|
| Beranda | Semua | Dashboard saldo & transaksi terbaru |
| Arus Kas | Semua (edit: Bendahara+) | CRUD transaksi masuk/keluar |
| Transfer Rekening | Semua (tambah: Bendahara+) | Pindah dana antar rekening |
| Laporan Periodik | Semua | Laporan tanggal bebas + PDF/Excel |
| Laporan Bulanan | Semua | Laporan bulanan detail + PDF/Excel |
| Laporan Tahunan | Semua | Rekap tahunan per bulan + PDF/Excel |
| Kegiatan | Semua (edit: Bendahara+) | Jadwal & dokumentasi kegiatan |
| Master Rekening | Admin | CRUD daftar rekening |
| Master Kategori | Admin | CRUD kategori kas |
| Hak Akses | Admin | Kelola peran pengguna |

---

## Peran (Roles)

| Peran | Lihat Data | Input/Edit/Hapus | Kelola User |
|-------|-----------|-----------------|-------------|
| **Admin** | ✅ | ✅ | ✅ |
| **Bendahara** | ✅ | ✅ | ❌ |
| **Viewer** | ✅ | ❌ | ❌ |

---

## Troubleshooting

**Q: Error "Class not found" setelah install**
```bash
composer dump-autoload
php artisan optimize:clear
```

**Q: Upload foto tidak berfungsi**
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

**Q: Saldo kategori tidak akurat**
```bash
php artisan mushola:recalculate
```

**Q: Error 500 di production**
```bash
# Cek log
tail -f storage/logs/laravel.log
# Pastikan APP_DEBUG=false dan key sudah di-generate
```

---

*Semoga aplikasi ini bermanfaat untuk pengelolaan keuangan mushola. Barakallah.*
