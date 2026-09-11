# SENYUM Neo-Brutalist Design System — Kretek Senyum 2.0

> Flat, kontras tinggi, border hitam tegas, hard shadow tanpa blur.
> Larangan: NO dark mode, NO glassmorphism, NO gradient berlebihan, NO pill everywhere.

## 1. Colors

| Token | Hex | Pakai untuk |
|---|---|---|
| `--senyum-cyan` | `#0BBCD6` | Aksen utama, panel brand, frame kemasan |
| `--senyum-cyan-dark` | `#0899AE` | Hover cyan |
| `--senyum-cyan-ink` | `#062A30` | Teks di atas cyan |
| `--senyum-yellow` | `#FFD21F` | CTA, highlight, selection, badge pending |
| `--senyum-yellow-dark` | `#EAB308` | Hover yellow |
| `--senyum-black` | `#141414` | Border, teks, header tabel, tombol primer |
| `--senyum-white` | `#FFFFFF` | Card, input |
| `--senyum-paper` | `#FFF9EC` | Background halaman |
| `--senyum-gray` | `#6B7280` | Teks sekunder, badge suspended |
| `--senyum-danger` | `#E11D48` | Error, tombol bahaya |
| `--senyum-success` | `#16A34A` | Sukses, badge active |

Tailwind v4 `@theme` memetakan warna yang sama sebagai `bg-senyum-cyan`, `text-senyum-ink`,
`bg-senyum-yellow`, `bg-senyum-paper`, dst. Lihat `resources/css/app.css`.

```blade
<div class="bg-senyum-cyan text-senyum-ink border-2 border-senyum-black">Halo</div>
```

## 2. Typography

- Display: `'Archivo Black', 'Arial Black', sans-serif` → `font-display` / `.sn-section-title` / `.sn-card-title`.
- Body: `Inter, system-ui, ...` → default `body`.
- Mono: `'JetBrains Mono', ui-monospace, ...` → `font-mono`, badge, kicker, angka tabel.

Skala: xs 12px · sm 14px · base 16px · lg 18px · xl 20px · 2xl 24px · 3xl 30px · 4xl 36px · 5xl 48px.
Judul section memakai `clamp()` agar responsif. Semua heading display uppercase.

## 3. Borders, Shadows, Radius

```css
--sn-border: 2px solid #141414;
--sn-border-thick: 3px solid #141414;
--sn-shadow-sm: 4px 4px 0 #141414;
--sn-shadow-md: 6px 6px 0 #141414;
--sn-shadow-lg: 8px 8px 0 #141414;
--sn-radius-sm: 2px; --sn-radius-md: 4px; --sn-radius-lg: 6px;
```

Aturan: tombol & input border 2px + shadow 4px; card & tabel border 3px + shadow 6px;
modal & pack-frame shadow 8px. Jangan pakai `rounded-full` kecuali avatar/logo.

## 4. Buttons — `.sn-btn`

```blade
<x-senyum-button variant="yellow" href="https://wa.me/6281234567890">Pesan</x-senyum-button>
<x-senyum-button variant="primary" type="submit">Simpan</x-senyum-button>

<a class="sn-btn sn-btn-cyan" href="/produk">Lihat Produk</a>
<button class="sn-btn sn-btn-danger sn-btn-sm" type="button">Hapus</button>
```

- Base: uppercase display, border 2px, shadow 4px.
- Hover: `translate(-1px,-1px)` + shadow membesar. Active: `translate(2px,2px)` + shadow menyusut.
- Varian: `.sn-btn-primary` (hitam/putih), `.sn-btn-yellow`, `.sn-btn-cyan`,
  `.sn-btn-danger`, `.sn-btn-ghost` (putih). Ukuran: `.sn-btn-sm`, `.sn-btn-lg`, `.sn-btn-block`.

## 5. Cards — `.sn-card`

```blade
<x-senyum-card title="Kemitraan Warung" kicker="Program" variant="yellow">
    Margin jelas, pasokan rutin, spanduk gratis.
</x-senyum-card>
```

Varian: default (putih), `cyan`, `yellow`, `paper`, `black`. Judul memakai `.sn-card-title`,
isi `.sn-card-body`.

## 6. Badges — `.sn-badge`

```blade
<x-senyum-badge status="active">Aktif</x-senyum-badge>
<x-senyum-badge status="pending">Menunggu</x-senyum-badge>
```

Map status → warna: `active/approved/success` hijau · `pending/warning` kuning ·
`rejected/danger` merah · `suspended` abu · `draft` abu muda · `featured` cyan.
Selalu mono, uppercase, border 2px.

## 7. Forms — `.sn-input` / `.sn-label` / `.sn-help` / `.sn-error`

```blade
<label class="sn-label" for="nama">Nama Warung</label>
<input id="nama" class="sn-input" type="text" placeholder="cth. Warung Barokah">
<p class="sn-help">Nama sesuai spanduk toko.</p>
<p class="sn-error">Nama wajib diisi.</p>
```

Focus: outline kuning 3px + `translate(-1px,-1px)` + shadow 3px. Error: `.sn-input--error`.

## 8. Tables — `.sn-table`

```blade
<x-senyum-data-table :headers="['Outlet', 'Qty', 'Total']">
    <tr><td>Warung Barokah</td><td class="sn-num">24</td><td class="sn-num">Rp480.000</td></tr>
</x-senyum-data-table>
```

Header hitam/putih uppercase mono; baris belang (`paper`); hover kuning muda;
angka mono rata kanan (`.sn-num`). Wrapper scroll-x dengan `min-width: 640px` + note mobile.

## 9. Sections, Hero, Packaging

```blade
<x-senyum-section number="01" title="Produk Kami" desc="Tiga varian, satu rasa berani.">
    ...
</x-senyum-section>

<section class="sn-hero-grid">...</section>

<x-senyum-product-emblem kicker="Unggulan" title="Senyum Original" price="Rp24.000">
    Tembakau Madura + cengkeh Zanzibar.
</x-senyum-product-emblem>
```

- `.sn-section-title`: display besar + blok nomor hitam/kuning.
- `.sn-hero-grid`: grid 40px samar di atas paper.
- `.sn-pack-frame`: bingkai kemasan — bg cyan, blok offset kuning, border hitam, smiley corner.
- Featured: `data-featured` → tap tampilkan detail (`.is-active`), reset 5 detik (lihat `app.js`).

## 10. Alerts, Empty, Skeleton, Modal, Stat

```blade
<x-senyum-alert type="warning" title="Stok menipis">Sisa 12 slop di gudang barat.</x-senyum-alert>
<x-senyum-empty-state title="Belum ada outlet" message="Tambahkan outlet pertamamu." action="Tambah" href="/distributor/outlet" />
<x-senyum-stat label="Outlet aktif" value="128" sub="+6 bulan ini" />
<x-senyum-modal id="hapus-modal" title="Hapus data?"><p>Data tidak bisa dikembalikan.</p></x-senyum-modal>
<div class="sn-skeleton h-6 w-1/2"></div>
```

Alert: border kiri 8px + hard shadow; varian `info/action/warning/success/danger`,
tombol `data-alert-close` menutup via JS. Modal dibuka via `data-modal-open="#hapus-modal"`.

## 11. Spacing

Basis 4px: `--sn-space-1` (4px) → `--sn-space-16` (64px). Card padding 24px
(16px mobile); section margin 40px; gap grid 16–24px.

## 12. Responsive

- `sm 640` · `md 768` · `lg 1024` · `xl 1280`.
- Tabel: scroll-x + note, bukan kartu ulang.
- `.sn-hide-mobile` / `.sn-show-mobile` untuk toggle cepat.
- Distributor: nav desktop penuh; mobile sticky bottom action 5 item
  (Home · Outlet · Visit · Order · More).
- Developer: sidebar desktop; mobile hamburger + drawer.

## 13. Motion

- Transisi 120–180ms `ease-out` saja. Intro smoke `~2.5s`, sekali per session
  (`sessionStorage sn_intro_seen`), klik/Esc mempercepat.
- `prefers-reduced-motion`: semua animasi mati, intro tidak tampil, shimmer skeleton mati.

## 14. Accessibility

- Semantic: `header/nav/main/footer`, `article` card, `th scope="col"`, `role="alert/dialog"`.
- Skip-link di setiap layout. Semua ikon/logo punya `aria-label` / `alt`.
- `:focus-visible` outline kuning 3px. Kontras teks ≥ 4.5:1 (ink `#062A30` di atas cyan,
  hitam di atas kuning). Jangan sampaikan info hanya lewat warna — selalu ada label teks.
- Nomor WhatsApp: **jangan hardcode** — pakai `config('senyum.whatsapp_number', '6281234567890')`.
