@extends('layouts.distributor')

@section('title', 'Bantuan — Field Workspace')

@section('content')
<p class="sn-kicker">System &middot; Bantuan</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Butuh Bantuan?</h1>
<p class="mt-1 text-sm text-neutral-600">Jawaban cepat untuk kerja harianmu. Kalau belum terjawab, chat developer langsung.</p>

<div class="grid gap-3 mt-5">
    <div class="sn-card">
        <h2 class="font-display font-black uppercase">Bagaimana cara jual ke outlet?</h2>
        <p class="text-sm text-neutral-600 mt-1">Buka <strong>Pemesanan</strong> → pilih outlet → tambah produk → kirim. Atau catat cepat dari <strong>Transaksi</strong>. Stok otomatis berkurang dari stok distributormu.</p>
    </div>
    <div class="sn-card">
        <h2 class="font-display font-black uppercase">Bagaimana jual ecer bekerja?</h2>
        <p class="text-sm text-neutral-600 mt-1">Buka <strong>Jual Ecer</strong> → isi nama pembeli (opsional) → pilih outlet sumber → tambah baris produk → CATAT PENJUALAN. Cocok untuk pembeli akhir satuan.</p>
    </div>
    <div class="sn-card">
        <h2 class="font-display font-black uppercase">Stok habis, bagaimana?</h2>
        <p class="text-sm text-neutral-600 mt-1">Buka <strong>PO (Minta Stok)</strong> → pilih DIKIRIM atau PICK-UP → isi tanggal & produk → lampirkan bukti bayar bila DIKIRIM → KIRIM PO. Pantau statusnya di halaman yang sama.</p>
    </div>
    <div class="sn-card">
        <h2 class="font-display font-black uppercase">Outlet tutup/pindah?</h2>
        <p class="text-sm text-neutral-600 mt-1">Buka <strong>Outlet</strong> → nonaktifkan lewat tombol status (dengan konfirmasi). Data lama tetap tersimpan, tidak hilang. Hapus hanya bila benar-benar salah input.</p>
    </div>
    <div class="sn-card">
        <h2 class="font-display font-black uppercase">Lupa follow-up kunjungan?</h2>
        <p class="text-sm text-neutral-600 mt-1">Isi tanggal <strong>tindak lanjut</strong> setiap mencatat kunjungan. Pengingatnya muncul otomatis di dashboard.</p>
    </div>
</div>

<div class="sn-card sn-card-yellow mt-5 text-center">
    <h2 class="sn-card-title">Masih buntu? Chat Developer</h2>
    <p class="text-sm mb-3">Ceritakan masalahmu, kami bantu sampai beres.</p>
    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sn-btn sn-btn-primary w-full sm:w-auto">Chat via WhatsApp</a>
</div>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Kembali ke <a class="underline font-bold" href="{{ route('distributor.dashboard') }}">dashboard</a> dan lanjutkan tugas harianmu.</x-senyum-alert>
</div>
@endsection
