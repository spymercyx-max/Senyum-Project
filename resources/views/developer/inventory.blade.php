@extends('layouts.developer')

@section('title', 'Inventory — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Product · Inventory</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Inventory</h1>
        <p class="text-sm text-neutral-600 mt-1">Pantau dan sesuaikan stok semua produk.</p>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif
    @if ($errors->any())
        <x-senyum-alert type="danger" title="Periksa lagi">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-senyum-alert>
    @endif

    <x-senyum-card title="Papan stok" kicker="Inventory board">
        <livewire:developer.inventory-board />
    </x-senyum-card>

    <x-senyum-card title="Penyesuaian manual (tanpa Livewire)" kicker="Fallback form">
        @if ($inventories->isEmpty())
            <x-senyum-empty-state title="Belum ada inventory" message="Data stok akan muncul di sini." />
        @else
            <div class="space-y-3">
                @foreach ($inventories as $inv)
                    <form method="POST" action="{{ route('developer.inventory.adjust', $inv) }}" class="border-2 border-black rounded-md p-3 bg-white grid sm:grid-cols-4 gap-2 items-end">
                        @csrf
                        <div class="sm:col-span-2">
                            <p class="font-bold text-sm">{{ $inv->product?->name ?? '—' }}</p>
                            <p class="font-mono text-xs text-neutral-500">Stok {{ $inv->stock }} · Cadangan {{ $inv->reserved }} · Tersedia {{ $inv->available }}</p>
                        </div>
                        <div>
                            <label class="sn-label" for="delta-{{ $inv->id }}">Selisih</label>
                            <input id="delta-{{ $inv->id }}" name="delta" type="number" class="sn-input" placeholder="10 / -5" required>
                        </div>
                        <div class="flex gap-2">
                            <input name="reason" type="text" class="sn-input" placeholder="Alasan" maxlength="500" aria-label="Alasan penyesuaian">
                            <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm shrink-0">SIMPAN</button>
                        </div>
                    </form>
                @endforeach
            </div>
        @endif
    </x-senyum-card>
</div>
@endsection
