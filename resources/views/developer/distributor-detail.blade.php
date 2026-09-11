@extends('layouts.developer')

@section('title', $user->name . ' — SENYUM Command Center')

@php
    $poBadge = fn ($s) => 'sn-badge--' . $s;
@endphp

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('developer.distributor') }}" class="sn-btn sn-btn-ghost sn-btn-sm mb-3">← Semua distributor</a>
        <p class="sn-kicker">Network · Detail distributor</p>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl">{{ $user->name }}</h1>
            <x-senyum-badge status="{{ $user->distributorStatus() ?? 'pending' }}">{{ strtoupper($user->distributorStatus() ?? 'PENDING') }}</x-senyum-badge>
        </div>
        <p class="font-mono text-xs text-neutral-500 mt-1">{{ '@' . $user->username }} · bergabung {{ $user->created_at?->translatedFormat('d M Y') }}</p>
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

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Profil" kicker="Profile">
            <dl class="text-sm space-y-2">
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Nama</dt><dd class="font-bold">{{ $user->name }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Username</dt><dd class="font-mono">{{ '@' . $user->username }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Telepon</dt><dd class="font-mono">{{ $user->phone ?? '—' }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">WhatsApp</dt><dd class="font-mono">{{ $user->profile?->whatsapp ?? '—' }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Kota</dt><dd>{{ $user->profile?->city ?? '—' }} {{ $user->profile?->district ? '· ' . $user->profile->district : '' }}</dd></div>
                <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Pengalaman</dt><dd>{{ $user->profile?->experience ?? '—' }}</dd></div>
                @if ($user->profile?->notes)
                    <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Catatan</dt><dd>{{ $user->profile->notes }}</dd></div>
                @endif
            </dl>
        </x-senyum-card>

        <x-senyum-card title="Wilayah & Status" kicker="Territory">
            <p class="text-sm">Wilayah: <strong>{{ $user->distributorProfile?->territory ? $user->distributorProfile->territory->city . ' – ' . $user->distributorProfile->territory->district : 'Belum ditempatkan' }}</strong></p>
            <p class="text-sm mt-1">Disetujui: <strong>{{ $user->distributorProfile?->approved_at?->translatedFormat('d M Y H:i') ?? '—' }}</strong></p>
            <p class="text-sm mt-1">Oleh: <strong>{{ $user->distributorProfile?->approver?->name ?? '—' }}</strong></p>

            <div class="mt-4">
                <livewire:developer.distributor-approval :userId="$user->id" />
            </div>
        </x-senyum-card>
    </div>

    <x-senyum-card title="Tempatkan ke wilayah" kicker="Assign territory">
        <livewire:developer.territory-board />
    </x-senyum-card>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Outlet ({{ $outletCount }})" kicker="Outlets">
            @if ($outlets->isEmpty())
                <x-senyum-empty-state title="Belum ada outlet" message="Distributor ini belum mendaftarkan outlet." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($outlets as $outlet)
                        <li class="flex items-center gap-2 border-2 border-black rounded-md px-3 py-2 bg-white flex-wrap">
                            <div class="min-w-0">
                                <p class="font-bold truncate">{{ $outlet->name }}</p>
                                <p class="text-xs text-neutral-500">{{ $outlet->territory?->city ?? '—' }} · {{ $outlet->city }}</p>
                                @if ($outlet->latitude && $outlet->longitude)
                                    <p class="font-mono text-[11px] text-neutral-500">{{ $outlet->latitude }}, {{ $outlet->longitude }}</p>
                                @endif
                            </div>
                            <x-senyum-badge status="{{ $outlet->status === 'active' ? 'active' : 'pending' }}" class="ml-auto">{{ strtoupper($outlet->status) }}</x-senyum-badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Pesanan (PO)" kicker="Purchase orders">
            @if ($orders->isEmpty())
                <x-senyum-empty-state title="Belum ada PO" message="Distributor ini belum membuat purchase order." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($orders as $po)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><a href="{{ route('developer.po.show', $po) }}" class="font-mono font-bold underline">{{ $po->code }}</a> · <span class="sn-badge {{ $poBadge($po->status) }}">{{ strtoupper($po->status) }}</span></p>
                            <p class="text-xs text-neutral-500">{{ $po->items->count() }} item · Rp{{ number_format($po->total_amount, 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Transaksi" kicker="Transactions">
            @if ($transactions->isEmpty())
                <x-senyum-empty-state title="Belum ada transaksi" message="Transaksi penjualan akan muncul di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($transactions as $tx)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><strong class="font-mono">{{ $tx->code }}</strong> · {{ $tx->outlet?->name ?? '—' }}</p>
                            <p class="text-xs text-neutral-500">Rp{{ number_format($tx->total_amount, 0, ',', '.') }} · {{ $tx->created_at?->translatedFormat('d M Y') }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Jejak aktivitas" kicker="Activity">
            @if ($activity->isEmpty())
                <x-senyum-empty-state title="Belum ada aktivitas" message="Aktivitas distributor akan tercatat di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($activity as $log)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><strong>{{ $log->actor?->name ?? 'Sistem' }}</strong> · <span class="font-mono text-xs">{{ $log->action }}</span></p>
                            <p class="font-mono text-xs text-neutral-500">{{ $log->created_at?->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>
    </div>
</div>

<x-senyum-modal id="modal-approve" title="Setujui distributor?" confirm="Ya, setujui">
    <p>Distributor <strong>{{ $user->name }}</strong> akan disetujui dan bisa mulai berjualan.</p>
    <form method="POST" action="{{ route('developer.distributor.approve', $user) }}" class="mt-4">
        @csrf
    </form>
</x-senyum-modal>

<x-senyum-modal id="modal-reject" title="Tolak pengajuan?" confirm="Ya, tolak">
    <p>Pengajuan <strong>{{ $user->name }}</strong> akan ditolak. Tulis alasan agar mudah dipahami.</p>
    <form method="POST" action="{{ route('developer.distributor.reject', $user) }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <label class="sn-label" for="reject-reason">Alasan (opsional)</label>
            <textarea id="reject-reason" name="reason" rows="3" class="sn-textarea" maxlength="500" placeholder="Contoh: data wilayah belum lengkap"></textarea>
        </div>
    </form>
</x-senyum-modal>

<x-senyum-modal id="modal-suspend" title="Tangguhkan distributor?" confirm="Ya, tangguhkan">
    <p>Akun <strong>{{ $user->name }}</strong> akan ditangguhkan sementara.</p>
    <form method="POST" action="{{ route('developer.distributor.suspend', $user) }}" class="mt-4 space-y-3">
        @csrf
        <div>
            <label class="sn-label" for="suspend-reason">Alasan (opsional)</label>
            <textarea id="suspend-reason" name="reason" rows="3" class="sn-textarea" maxlength="500" placeholder="Contoh: pelanggaran sementara"></textarea>
        </div>
    </form>
</x-senyum-modal>
@endsection
