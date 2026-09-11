@extends('layouts.distributor')

@section('title', 'Notifikasi — Field Workspace')

@section('content')
<p class="sn-kicker">System &middot; Notifikasi</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Kabar Terbaru</h1>
<p class="mt-1 text-sm text-neutral-600">Info dari developer: status PO, persetujuan, dan pengumuman. Membuka halaman ini menandai semua sudah dibaca.</p>

@if ($notifications->isEmpty())
    <div class="mt-5"><x-senyum-empty-state title="Belum ada kabar" message="Notifikasi baru akan muncul di sini." /></div>
@else
    <div class="grid gap-2 mt-5">
        @foreach ($notifications as $n)
            <article class="sn-card !p-4">
                <div class="flex items-center gap-2">
                    <x-senyum-badge status="info">{{ $n->type ?? 'info' }}</x-senyum-badge>
                    <time class="font-mono text-[11px] text-neutral-500 ml-auto">{{ $n->created_at->format('d M Y H:i') }}</time>
                </div>
                <h2 class="font-bold mt-2">{{ $n->title }}</h2>
                @if ($n->body)<p class="text-sm text-neutral-600 mt-1">{{ $n->body }}</p>@endif
            </article>
        @endforeach
    </div>
    <div class="mt-3">{{ $notifications->links() }}</div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Ada yang kurang jelas? Tanya lewat <a class="underline font-bold" href="{{ route('distributor.bantuan') }}">halaman bantuan</a>.</x-senyum-alert>
</div>
@endsection
