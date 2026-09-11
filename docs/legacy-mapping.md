# Legacy Mapping — Kretek Senyum

Status basis data saat transisi ke arsitektur baru (2026-09-09):

- `database/database.sqlite` yang ada HANYA berisi **data demo sintetis**
  hasil seeder (akun developer/distributor contoh, produk contoh, dsb).
- **Tidak ada data bisnis riil** (tidak ada Toko/Outlet/Mitra/transaksi
  nyata dari operasional). Karena itu reset bersih (`migrate:fresh --seed`)
  aman dan tidak menghilangkan data berharga.
- Jalur import aman tetap tersedia: `App\Services\LegacyImportService`
  (staging → mapping → validasi → transaksi → deteksi duplikat →
  verifikasi FK). Jangan DROP/TRUNCATE database sumber.

## Jika di kemudian hari ada database lama yang harus diimpor

| Field lama (contoh) | Field baru | Transformasi | Nullable/Default | Validasi |
|---|---|---|---|---|
| toko.nama / mitra.nama | outlets.name | trim, maks 255 char | required | string max:255 |
| toko.alamat | outlets.address | trim | required | string |
| toko.kota | outlets.city + territories.city | normalisasi (lowercase, collapse spasi) via TerritoryService | required | string max:100 |
| toko.kecamatan | outlets.district + territories.district | normalisasi via TerritoryService | required | string max:100 |
| toko.telp / hp | outlets.phone | digit + `+`, maks 30 | nullable | string max:30 |
| link maps (bebas) | outlets.google_maps_url + latitude/longitude | parse via MapService (otoritatif dari link) | nullable; koordinat wajib valid (-90..90, -180..180, bukan 0,0) | UnresolvableMapsLinkException → baris DITOLAK, jangan ditebak |
| distributor.nama | users.name + profiles | trim | required | string max:255 |
| distributor.wilayah | distributor_profiles.territory_id | findOrCreate territory (case-insensitive) | nullable FK nullOnDelete | exists territories |
| produk.nama/harga | products.name/price (+tiers) | harga ke integer rupiah | price default 0 | integer min:0 |
| stok | inventories.stock | integer >= 0 | default 0 | tolak negatif |
| po/transaksi lama | purchase_orders/transactions (+items +snapshots) | snapshot product_name/sku ikut disalin; status lama dipetakan ke DRAFT/DISETUJUI/...; histori diisi actor=null note "legacy import" | status default draft | tolak transisi invalid |

## Aturan keras

1. Jangan mutasi database sumber. Salin → staging → validasi → import.
2. Baris yang gagal validasi dicatat di laporan, TIDAK menghentikan batch
   yang valid (transaksi per baris/batch kecil).
3. Duplikat dideteksi (username, SKU, slug, territory ternormalisasi,
   kode PO/transaksi) → skip + laporkan, jangan overwrite.
4. Verifikasi akhir: hitung baris per tabel, cek orphan FK
   (outlet tanpa territory/distributor, item tanpa produk), cek stok
   negatif, cek total PO = sum(subtotal).
5. Koordinat tidak valid → outlet tetap diimpor TANPA koordinat
   (location_source=null) + flag perlu dilengkapi, KECUALI user
   memberi link spesifik yang gagal resolve → tolak baris + pesan.
