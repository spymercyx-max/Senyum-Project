# User Flows — KRETEK SENYUM 2.0

## 1. Visitor (publik, tanpa akun)

```
Home (intro asap 1x/session)
 → HERO (headline + CTA + featured product)
 → ABOUT (01 Tentang Senyum)
 → COMPOSITION (02 Komposisi, 13+ rempah)
 → CHARACTER (03 Karakter Senyum, faktual + disclaimer)
 → PRODUCTS (maks 3 kartu)
 → ORDER GUIDE (Cara Memesan 01–04)
 → PARTNERSHIP teaser
 → FOOTER (navigasi + akun + kontak WA + notice 18+)
```

Order flow:

```
Produk → /produk → /produk/{slug} (review harga/SKU/ketersediaan)
 → [Pesan Melalui WhatsApp] → chat admin (inquiry, bukan order otomatis)
 → sepakati jumlah/pengiriman di chat → transaksi eksternal
```

Partnership flow:

```
Kemitraan (baca 01–05) → [Konsultasi Kemitraan] (WA developer)
 → /register → pending → review developer → approved
 → Distributor workspace
```

## 2. Calon Distributor → Distributor

```
Register (nama, username, WA, kota, kecamatan, pengalaman, password)
 → validasi → normalisasi kota/kecamatan → find-or-create territory
 → user(distributor) + profile + distributor_profile(pending) [atomic]
 → auto-login → /distributor/pending
```

Setelah approved (notifikasi masuk):

```
WELCOME TO SENYUM + territory + checklist:
 01 Lengkapi Profil → /distributor/profil
 02 Review Territory → /distributor/peta
 03 Review Produk → stok board
 04 Tambah Outlet → /distributor/outlet/create
 05 Catat aktivitas → order/visit/transaksi
```

Operasional harian (`/distributor/dashboard` → ACTION TODAY):

```
Outlet: lihat/tambah/nonaktifkan + detail (transaksi, visit)
Visit: catat tanggal/outlet/notes/follow-up (lokasi opsional)
Order/Jual: pilih outlet → produk + qty → review → simpan (stok berkurang)
PO: buat (produk + qty) → review → submit → status terpantau
Titip Jual: catat konsinyasi per outlet×produk
Pembukuan: ringkasan omzet + PO + konsinyasi berjalan
Peta: outlet + link Google Maps
```

Status non-approved: pending / rejected / suspended — workspace
operasional terkunci sampai approved.

## 3. Developer (Control Center)

```
Login → /developer/dashboard:
 ACTION REQUIRED (pending distributor, pending PO, low stock,
 inactive territory, alert operasional)
 → Business Overview → Territory Performance → Inventory
 → Recent Activity
```

Alur kelola:

```
Distributor: tab PENDING/APPROVED/REJECTED/SUSPENDED
 → detail (profil, territory, outlet, order, transaksi, aktivitas)
 → Approve / Reject / Suspend / Assign Territory (konfirmasi modal + log)
Wilayah: tabel CITY/DISTRICT/DISTRIBUTOR/OUTLET/ACTIVE/ACTIVITY/STATUS
 → detail → assign/reassign distributor
Produk: create/edit/archive (arsip, bukan hapus) + image + SKU +
 harga + status + [Set Featured] (unik, konfirmasi)
Inventory: Product/Stock/Reserved/Available/Threshold/Status
 (Healthy/Low/Critical/Out) + adjust
PO: view/create/approve/update/track (draft→submitted→approved→
 processing→completed, cancelled)
Laporan: sales, performa produk/territory/distributor/outlet,
 mutasi inventory. Peta: sebaran outlet.
Notifikasi: approval, inventory, PO, sistem. Profil: data developer.
```

Semua aksi penting menulis `activity_logs` (actor, action, entity,
entity_id, metadata, timestamp) dan notifikasi terkait.
