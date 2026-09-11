@extends('layouts.distributor')

@section('title', 'Kunjungan Outlet — Field Workspace')

@section('content')
<p class="sn-kicker">Network &middot; Visit</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Kunjungan Outlet</h1>
<p class="mt-1 text-sm text-neutral-600">Catat setiap kunjungan: kapan datang, apa hasilnya, kapan perlu datang lagi.</p>

<div class="mt-5">
    @livewire('distributor.visit-form')
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Semua Riwayat</h2>
<form method="GET" action="{{ route('distributor.visit') }}" class="flex gap-2 mb-3">
    <select name="outlet_id" class="sn-select" onchange="this.form.submit()" aria-label="Saring per outlet">
        <option value="">Semua outlet</option>
        @foreach ($outlets as $o)
            <option value="{{ $o->id }}" @selected($filterOutlet == $o->id)>{{ $o->name }}</option>
        @endforeach
    </select>
    @if ($filterOutlet)
        <a href="{{ route('distributor.visit') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Reset</a>
    @endif
</form>

@if ($visits->isEmpty())
    <x-senyum-empty-state title="Belum ada riwayat" message="Catatan kunjungan lengkap akan muncul di sini." />
@else
    <div class="grid gap-2">
        @foreach ($visits as $v)
            <div class="sn-card !p-3 text-sm">
                <strong>{{ $v->outlet?->name ?? '—' }}</strong>
                <x-senyum-badge :status="$v->status">{{ $v->status }}</x-senyum-badge>
                <span class="block text-neutral-600 mt-1">{{ $v->visited_at?->format('d M Y') }}{{ $v->notes ? ' — ' . \Str::limit($v->notes, 80) : '' }}</span>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $visits->withQueryString()->links() }}</div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Kunjungan yang butuh tindak lanjut muncul di <a class="underline font-bold" href="{{ route('distributor.dashboard') }}">dashboard</a> sebagai pengingat harian.</x-senyum-alert>
</div>
@endsection
