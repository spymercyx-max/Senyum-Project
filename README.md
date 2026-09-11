# KRETEK SENYUM 2.0

Aplikasi Laravel baru (arsitektur bersih) untuk brand kretek tangan **SENYUM** —
satu brand, tiga experience, satu design system Neo-Brutalist.

- **Visitor** — website publik (brand experience, tanpa login)
- **Distributor** — Field Workspace (operasional mobile-first)
- **Developer** — Control Center (pusat kendali bisnis)

Stack: Laravel 12 (PHP 8.2, upgrade-ready ke Laravel 13 / PHP 8.3+) +
Blade + Livewire 4 + Tailwind CSS 4 + Vite. SQLite untuk development,
MySQL/MariaDB untuk production. PHP-first, tanpa React/Vue/Angular.

> Catatan versi: spec meminta Laravel 13 + PHP 8.3+. Lingkungan
> development ini memakai PHP 8.2 sehingga proyek dipasang pada
> Laravel 12 (^12.0). Kode ditulis agar siap naik ke Laravel 13
> (tanpa API deprecated, enum gaya PHP 8.1, tanpa fitur khusus 8.3).

## Requirements

- PHP ^8.2 (ext: mbstring, sqlite3/mysqlnd, gd/fileinfo, zip, curl, openssl)
- Composer 2
- Node 20+ & npm 10+
- MySQL/MariaDB (production) atau SQLite (development)

## Instalasi (development)

```powershell
composer install
npm install
Copy-Item .env.example .env   # atau: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Atau mode dev (server + queue + log + vite):

```powershell
composer dev
```

Buka http://127.0.0.1:8000

## Environment penting

```ini
APP_NAME="Kretek Senyum"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite            # production: mysql + DB_HOST/PORT/DATABASE/USERNAME/PASSWORD
SENYUM_WA=6281234567890         # nomor WA pemesanan publik
SENYUM_DEV_WA=6281234567890     # nomor WA developer (konsultasi kemitraan)
DEV_PASSWORD=fullsenyum         # password awal akun development (seeder)
SENYUM_PURGE_RETENTION_DAYS=60  # retensi soft-delete sebelum purge permanen

# Google Maps Platform (opsional — tanpa key, peta tampil failure state + daftar)
MAP_PROVIDER=google
GOOGLE_MAPS_API_KEY=
GOOGLE_MAPS_GEOCODING_API_KEY=
GOOGLE_MAPS_DIRECTIONS_API_KEY=
```

Jangan commit secret. Kredensial tidak pernah ditampilkan di halaman login.

## Akun bawaan (seeder, development saja)

| Role | Username | Password default | Nama tampilan |
|---|---|---|---|
| Developer | `X-Mercy` | `fullsenyum` (atau `DEV_PASSWORD`) | X-Mercy |
| Developer | `Madcapone` | `fullsenyum` | Madcapone |
| Developer | `Ahmadalkaff` | `fullsenyum` | Ahmadalkaff |
| Distributor | `X-Mercy-Dist` | `fullsenyum` | X-Mercy |

Catatan: `users.username` unik + satu halaman login tanpa role selector,
sehingga distributor memakai `X-Mercy-Dist` (keputusan deterministik;
lihat `docs/architecture.md`). Diagnosis akses:
`php artisan senyum:doctor` (perbaiki: `--fix`).

Satu halaman login: `/login` (username **atau** email + password, tanpa
pemilih role). Role ditentukan server, redirect otomatis:

- Developer → `/developer/dashboard`
- Distributor approved → `/distributor/dashboard`
- pending / rejected / suspended → layar status masing-masing

Pendaftaran distributor: `/register` (nama, username, WhatsApp, kota,
kecamatan, pengalaman, password). Kota+kecamatan dinormalisasi dan
dipetakan ke `territories` (anti-duplikat huruf besar/kecil & spasi);
status awal `pending`, butuh approval Developer.

## Database

Lihat `docs/database.md` (tabel, relasi, indeks) dan
`docs/architecture.md` (role, auth, service, routing, otorisasi).

Perintah berguna:

```powershell
php artisan migrate --seed     # setup awal
php artisan migrate:fresh --seed --force   # reset total (DEV SAJA)
php artisan db:seed --force
```

Migrasi data lama (Toko/Outlet/Mitra/Distributor/Territory/Produk/PO/
Transaksi) dilakukan aman via `App\Services\LegacyImportService`
(staging → mapping → validasi → transaksi → deteksi duplikat →
verifikasi FK). Tidak ada DROP/TRUNCATE/blind DELETE.

## Testing

```powershell
php artisan test
```

Mencakup: auth (developer/distributor/pending/rejected), otorisasi
(403 + redirect), registrasi & reuse territory case-insensitive,
featured product uniqueness (hanya produk aktif), WhatsApp URL, order
flow, render halaman publik, seed akun + clean state, parser link
Google Maps, tier pricing, alur PO delivery & pickup, idempotensi
double-approval, jual ecer multi-item, outlet + koordinat, soft delete
+ purge, otorisasi Map API.

## Build & deploy

```powershell
npm run build          # hasil ke public/build
php artisan config:cache; php artisan route:cache; php artisan view:cache
```

Production: set `APP_ENV=production APP_DEBUG=false`, gunakan MySQL,
jalankan `migrate --force`, pastikan `storage:link` dan cron queue bila
diperlukan.

## Dokumentasi

- `docs/architecture.md` — role, auth, service, routing, otorisasi
- `docs/database.md` — tabel, relasi, indeks, constraint
- `docs/design-system.md` — token warna, tipografi, tombol, kartu,
  form, tabel, shadow, responsif, motion
- `docs/user-flows.md` — alur Visitor, Distributor, Developer
- `docs/legacy-mapping.md` — strategi import data lama yang aman

## Perintah operasional

```powershell
php artisan senyum:doctor            # cek akun + database
php artisan senyum:doctor --fix      # perbaiki akun dev (non-production)
php artisan senyum:purge-expired-deleted-data --dry-run   # simulasi purge
php artisan schedule:list           # lihat jadwal (purge harian 02:00)
```

## Tanggung jawab konten

Produk tembakau: khusus 18+. Tidak ada klaim sehat/aman/obat/terapi.
Pesan otomatis WhatsApp bersifat inquiry — bukan konfirmasi order.
Kemitraan tidak menjanjikan profit/ROI pasti.
