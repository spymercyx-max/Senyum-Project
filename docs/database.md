# Database — KRETEK SENYUM 2.0

Dev: `sqlite` · Production: `mysql`. Semua migrasi baru memakai prefix
`2026_09_08_*` dan kompatibel dengan kedua driver (hindari fitur
khusus MySQL; `decimal(10,7)` untuk koordinat).

## Tabel & kolom

| Tabel | Kolom penting | Index / unique |
|---|---|---|
| `users` | id, name, `username` unique, `email` nullable unique, phone nullable, password, `role` default `distributor`, `status` default `active`, `last_login_at` nullable, rememberToken, timestamps (+`email_verified_at`) | index username, role, status |
| `territories` | id, city, district, city_normalized, district_normalized, code nullable unique, status default `active`, notes nullable | unique(city_normalized, district_normalized); index city, district |
| `profiles` | id, user_id fk→users cascade (unique), avatar, city, district, experience, whatsapp, notes | — |
| `distributor_profiles` | id, user_id fk→users cascade unique, territory_id nullable fk→territories nullOnDelete, status default `pending`, approved_at, approved_by nullable fk→users | index territory_id, status |
| `products` | id, name, slug unique, short_description, description, price unsigned, sku unique, image, status default `draft`, featured bool false, sort_order 0, ingredients, availability_note | index slug, featured, status |
| `product_images` | id, product_id fk cascade, path, disk default `public`, sort_order 0 | — |
| `inventories` | id, product_id fk cascade unique, stock 0, reserved 0, threshold 10 | — |
| `outlets` | id, territory_id fk, distributor_id fk→users, name, address, city, district, phone, latitude/longitude decimal(10,7), status default `active`, notes | index territory_id, distributor_id, status |
| `outlet_contacts` | id, outlet_id fk cascade, name, phone, role | — |
| `purchase_orders` | id, code unique, distributor_id fk, status default `draft`, notes, total_amount 0 | index distributor_id, status |
| `purchase_order_items` | id, purchase_order_id fk cascade, product_id fk restrict, qty, price, subtotal | — |
| `transactions` | id, code unique, distributor_id fk, outlet_id fk, status default `completed`, total_amount 0, notes, sold_at | index distributor_id, outlet_id |
| `transaction_items` | id, transaction_id fk cascade, product_id fk restrict, qty, price, subtotal | — |
| `visits` | id, distributor_id fk, outlet_id fk, visited_at, status default `done`, notes, follow_up_at, latitude/longitude | index outlet_id, distributor_id |
| `consignments` | id, distributor_id fk, outlet_id fk, product_id fk, qty, sold_qty 0, status default `active`, notes | — |
| `notifications` | id, user_id fk cascade, type default `info`, title, body, data json, read_at | index user_id, read_at |
| `activity_logs` | id, actor_id nullable fk→users nullOnDelete, action, entity, entity_id, metadata json, ip | index actor_id, action |
| `settings` | id, key unique, value text, type default `string` | — |
| `contacts` | id, name, whatsapp, phone, message, source default `public`, is_read false | — |
| `order_inquiries` | id, product_id nullable fk nullOnDelete, name, whatsapp, message, status default `new` | — |
| `password_reset_tokens`, `sessions`, `cache*`, `jobs*` | bawaan Laravel | — |

## Relasi utama

- `User 1—1 Profile`, `User 1—1 DistributorProfile`, `User → Territory`
  via `HasOneThrough(DistributorProfile)`.
- `Territory 1—N DistributorProfile`, `Territory 1—N Outlet`.
- `User(distributor) 1—N Outlet / PurchaseOrder / Transaction / Visit`.
- `Product 1—N ProductImage`, `Product 1—1 Inventory`.
- `PurchaseOrder 1—N PurchaseOrderItem`, `Transaction 1—N TransactionItem`.
- `Outlet 1—N OutletContact / Transaction / Visit / Consignment`.

## Aturan hapus

- Hapus user → profile, distributor_profile, outlet, PO, transaksi ikut
  terhapus (`cascade`); log aktivitas (`actor_id`) di-null-kan.
- Hapus produk → inventory & gambar ikut terhapus; item PO/transaksi
  memakai `restrict` agar riwayat tidak yatim.
- Hapus territory → `distributor_profiles.territory_id` di-null-kan;
  outlet dalam wilayah ikut terhapus (`cascade`).

## Enum status (string di DB)

`users.role`: developer|distributor · `users.status`: active|inactive|pending ·
`distributor_profiles.status`: pending|approved|rejected|suspended ·
`territories/outlet`: active|inactive(+pending outlet) ·
`products`: draft|active|archived · `purchase_orders`:
draft|disetujui|ditolak|dikirim|diambil|selesai (mode fulfillment:
delivery|pickup; otoritas tunggal `PurchaseOrderStatus::allowedNext()`) ·
stok (`InventoryService::stockStatus` + enum `StockStatus`):
healthy|low|critical|out.

## Skema tambahan (2026-09-09)

- `products`: +`distributor_price` (integer rupiah), +`deleted_at` (soft delete).
- `product_price_tiers`: product_id FK cascade, channel customer|distributor,
  min_qty, max_qty nullable (satu open-ended per channel), price integer.
  Deterministik: tier cocok pertama dari min_qty terkecil.
- `product_images`: +original_name, size, mime. Maks 5 gambar/produk (kode).
- `outlets`: +google_maps_url (URL asli user), +location_source
  (google_maps|manual_correction|legacy_import), +location_verified_at,
  +`deleted_at`. Peta selalu memakai latitude/longitude TERSIMPAN.
- `outlet_images`: outlet_id FK cascade, path, disk, original_name, size,
  mime, sort_order. Maks 5 gambar/outlet (kode).
- `purchase_orders`: +fulfillment (delivery|pickup), +order_date (tanggal
  bisnis, terpisah dari created_at), +payment_proof (path, wajib bila
  delivery), +tracking_number (wajib saat DIKIRIM), +delivery_note,
  +pickup_message, +rejection_reason (wajib saat DITOLAK).
- `po_status_histories`: purchase_order_id FK cascade, from nullable, to,
  actor_id FK nullOnDelete, note, timestamps. Setiap transisi tercatat.
- `purchase_order_items` + `transaction_items`: +product_name, +sku
  (snapshot anti-hilang saat produk dihapus/diarsip).
- `transactions`: +buyer_name nullable, +type (outlet|retail),
  +`deleted_at`. `sold_at` = tanggal bisnis (jangan pakai created_at).
- `distributor_stocks`: distributor_id + product_id unique, qty.
  Satu-satunya "stok milik distributor". +saat PO disetujui, −saat
  penjualan tercatat. Tidak pernah transfer seluruh stok.
- `consignments`: TETAP ADA untuk integritas historis, tapi workflow
  Titip Jual aktif DIHAPUS (tanpa route/form/nav).
- Retensi: soft-deleted produk/outlet/transaksi di-purge permanen
  setelah `senyum.purge_retention_days` (default 60) via
  `senyum:purge-expired-deleted-data` (jadwal harian 02:00).
- Seed bersih: hanya akun (3 developer + 1 distributor), settings.
  Produk/outlet/transaksi/PO/territory dimulai KOSONG.
