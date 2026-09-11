# Architecture — KRETEK SENYUM 2.0 (Backend Foundation)

Stack: Laravel 12 + PHP 8.2 + Livewire 4 (upgrade-ready Laravel 13:
tanpa API deprecated; enum PHP 8.1 style, tanpa `readonly class`/typed
const 8.3; policy didaftarkan via `Gate::policy` di
`AppServiceProvider`).

## Roles & auth

- `users.role`: `developer` (operator pusat) | `distributor` (mitra).
- `distributor_profiles.status`: `pending` → `approved` (atau
  `rejected`/`suspended`). Login memakai `username` + password
  (`Auth::attempt(['username', ...])` didukung Eloquent provider).
- Middleware (alias di `bootstrap/app.php`):
  - `role:<r>` (`RoleMiddleware`) — abort 403 bila peran tak cocok.
  - `distributor.status:<allowed>` (`DistributorStatusMiddleware`) —
    developer lolos; distributor dicek status profilnya; bila tak
    diizinkan redirect ke `/distributor/{pending,rejected,suspended}`
    (atau route bernama bila ada).
  - `nocache` (`NoCacheMiddleware`) — header no-store untuk halaman privat.
- Request validasi: `RegisterDistributorRequest` (pendaftaran publik),
  `ProductRequest` (developer only via `authorize()`), `OutletRequest`,
  `TransactionRequest`, `PurchaseOrderRequest`, `VisitRequest`.

## Authorization

- Policy: `ProductPolicy`, `DistributorPolicy` (target model `User`),
  `TerritoryPolicy`, `OutletPolicy`, `TransactionPolicy`,
  `PurchaseOrderPolicy`; mapping di `AppServiceProvider::boot()`.
- Prinsip: developer akses penuh; distributor baca produk aktif saja,
  kelola hanya outlet/transaksi/PO miliknya (outlet boleh dibaca bila
  satu territory).

## Services (logika bisnis, bukan controller)

| Service | Tanggung jawab |
|---|---|
| `TerritoryService` | `normalize()` (lowercase+collapse whitespace), `findOrCreate()`, `normalizeCity/District` |
| `RegistrationService` | `register()` transaksional: territory → user(distributor) → profile → distributor_profile(pending) → activity log |
| `FeaturedProductService` | `setFeatured()` (satu produk featured dalam transaksi), `clearFeatured()` |
| `DistributorApprovalService` | `approve/reject/suspend/assignTerritory` + activity log + notifikasi distributor |
| `InventoryService` | `adjust/reserve/release`, `stockStatus()` (healthy\|low\|critical\|out dari available vs threshold) |
| `PurchaseOrderService` | `createOrder()` + hitung total transaksional; `updateStatus()` dengan matriks transisi + log |
| `TransactionService` | `createTransaction()` — validasi outlet milik distributor/satu territory, hitung total, kurangi stok via `InventoryService` dalam transaksi |
| `WhatsAppService` | nomor dari `Setting`/`config/senyum.php`; `waUrl()`, `productInquiryUrl()`, `partnershipUrl()` |
| `LegacyImportService` | staging import aman: `importTerritories/importOutlets/validateRow`, deteksi duplikat via normalized & nama+alamat, laporan `[created, skipped, errors]`, tanpa truncate |
| `SettingService` | `get/set/all` di atas model `Setting` (cache `rememberForever`) |

## Routing (milik agent lain — tidak disentuh)

- `routes/web.php`, `resources/views|css|js`, `app/Http/Controllers`
  sengaja tidak diubah. Konvensi yang diharapkan: `/developer/*`
  (guard `role:developer`), `/distributor/*` (guard
  `distributor.status:approved`), rute status
  `distributor.pending|rejected|suspended`.

## Support & config

- `config/senyum.php`: brand, `whatsapp_number`, `developer_whatsapp`,
  site title/desc, responsible notice (mirip `SettingSeeder`).
- `App\Support\Senyum`: `brand()`, `formatRupiah()`, `stockBadge()`.

## Seed & test

- Seeder idempoten (`updateOrCreate`): Setting → Developer
  (X-Mercy, Madcapone, Ahmadalkaff / `DEV_PASSWORD`, default `fullsenyum`)
  → Distributor (X-Mercy-Dist, nama tampilan X-Mercy, approved).
  Produk/outlet/transaksi/PO/territory/notifikasi: KOSONG (clean start).
- Keputusan konflik X-Mercy: `users.username` unik + satu halaman login
  tanpa role selector → developer memakai `X-Mercy`, distributor memakai
  `X-Mercy-Dist` (deterministik, tanpa ambiguitas); identitas "X-Mercy"
  dipertahankan di nama/profil distributor.
- Test: `tests/Feature` (Auth, LoginFlow, Registration, FeaturedProduct,
  TerritoryService, WhatsAppService, Authorization, PageRender,
  SeedAccounts, MapsParser, ProductTier, PurchaseOrderFlow,
  RetailTransaction, OutletMaps, SoftDeletePurge, MapApi) dan
  `tests/Unit` (normalize, status stok). DB test sqlite `:memory:`.

## Subsistem baru (2026-09-09)

- **PO state machine** (`PurchaseOrderStatus::allowedNext()` satu-satunya
  otoritas; `PurchaseOrderService::transition()` eksekutor): DRAFT →
  DISETUJUI → DIKIRIM/DIAMBIL → SELESAI; DRAFT → DITOLAK. Approval
  mengurangi stok pusat HANYA sejumlah qty (lockForUpdate + cek cukup)
  dan mengalokasikan ke `distributor_stocks`; idempoten terhadap klik
  ganda (transisi kedua dari status sama ditolak sebelum efek samping).
- **Stok distributor** (`DistributorStockService`): bertambah saat PO
  disetujui, berkurang saat penjualan (ecer/outlet). Penjualan TIDAK
  menyentuh stok pusat. Pembukuan membaca layanan ini (data nyata).
- **Harga bertingkat** (`ProductPricingService`): tier per channel,
  validasi overlap/duplikat/batas; resolusi deterministik + fallback
  ke harga dasar.
- **Maps** (`MapProviderInterface` → `GoogleMapsProvider`, `MapService`,
  `GoogleMapsLinkParser` murni): link user → koordinat tersimpan
  (short-link di-resolve + di-cache 30 hari). Tanpa API key: failure
  state ramah + daftar (tidak crash). API internal
  `/api/developer/map/*` (session auth + role developer + throttle 120)
  dengan Resources (tanpa password/token). Coverage territory:
  covered (outlet aktif + aktivitas ≤30 hari), partial, uncovered.
- **Upload** (`App\Support\ImageUpload`): validasi MIME asli
  (jpg/png/webp), maks 5 MB, nama hash; maks 5 gambar per produk/outlet;
  payment proof wajib untuk fulfillment delivery.
- **Soft delete + purge**: produk/outlet/transaksi (`SoftDeletes`);
  purge terjadwal harian via `routes/console.php`.
- **Timezone**: Asia/Jakarta. Uang: integer rupiah. UI: Indonesia
  (DRAFT/DISETUJUI/DITOLAK/DIKIRIM/DIAMBIL/SELESAI).
- **Laravel tetap 12** (PHP 8.2 di environment tidak memenuhi syarat
  Laravel 13); kode bebas API deprecated agar siap upgrade.

## Koreksi terarah (2026-09-10, tanpa reset DB)

- **Resolver short-link** (`GoogleMapsProvider::resolveShortUrl`): loop
  redirect manual (redirects off, baca header Location hop-per-hop,
  maks 5, timeout 8 dtk). Tiap hop: skema http/https, host allowlist
  Google (goo.gl/+subdomain, google.com/+subdomain, g.co/+subdomain),
  tolak IP literal, tolak DNS ke range privat (fail closed). Cache 30
  hari di `MapService`. Bukan fetcher URL arbitrary (anti-SSRF).
- **Prioritas parser** (`GoogleMapsLinkParser`): `!3d/!4d` (tempat) →
  `@lat,lng` (kamera/pin) → `?q/?query/?destination` → `/dir/` →
  `lat/lng`. Presisi penuh (float → decimal(10,7)). Terverifikasi live
  terhadap `https://maps.app.goo.gl/ARaPDYhML3oHWw549` → place
  (-7.9390937, 112.6247288), bukan kamera.
- **Kanal harga tertutup**: distributor → tier distributor → dasar
  distributor SAJA; customer → tier customer → dasar customer SAJA.
  Harga jual ke outlet = nilai per-transaksi (tidak menyentuh master).
  Harga auto ≤0 ditolak dengan pesan yang jelas.
- **Transaksi**: snapshot `cost_price` (acuan beli) + `price` (jual);
  koreksi via `updateTransaction` (delta stok agregat per produk,
  anti-negatif); void via `voidTransaction` (kembalikan qty utuh,
  status void + soft-delete, record lestari); riwayat append-only di
  `activity_logs` (created/corrected/voided + old/new + delta).
- **Kontak**: order 628980506754, developer 6281222226989 (Setting +
  config default; normalisasidigunakan `WhatsAppService`). Perbaikan
  cache: `SettingSeeder` memakai `Setting::set` (bust rememberForever).
- **Tanggal bisnis**: PO `order_date` bebas (dulu/today/mendatang);
  list PO developer menampilkan Tgl Pesan (order_date), bukan created_at.
- **Peta**: API +foto/telepon/outlet, statistik lokasi valid/belum
  valid, select kolom hemat, index lat/lng; UI fitBounds + InfoWindow
  lengkap + search→fokus.
