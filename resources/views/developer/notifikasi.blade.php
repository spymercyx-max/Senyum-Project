@extends('layouts.developer')

@section('title', 'Notifikasi — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <p class="sn-kicker">System · Notifikasi</p>
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Notifikasi</h1>
            <p class="text-sm text-neutral-600 mt-1">{{ $unreadCount }} belum dibaca.</p>
        </div>
        @if ($unreadCount > 0)
            <a href="{{ route('developer.notifikasi', ['read' => 'all']) }}" class="sn-btn sn-btn-tosca sn-btn-sm ml-auto">Tandai semua dibaca</a>
        @endif
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    @if ($notifications->isEmpty())
        <x-senyum-empty-state title="Tidak ada notifikasi" message="Kabar terbaru untuk developer akan muncul di sini." />
    @else
        <ul class="space-y-3">
            @foreach ($notifications as $n)
                <li class="sn-card !p-4 {{ $n->read_at ? '' : 'sn-card-yellow' }}">
                    <div class="flex items-start gap-2 flex-wrap">
                        <div class="min-w-0">
                            <p class="font-display font-bold uppercase text-sm">{{ $n->title }}</p>
                            @if ($n->body)
                                <p class="text-sm mt-1">{{ $n->body }}</p>
                            @endif
                            <p class="font-mono text-xs text-neutral-600 mt-1">{{ $n->type }} · {{ $n->created_at?->translatedFormat('d M Y H:i') }} ({{ $n->created_at?->diffForHumans() }})</p>
                        </div>
                        @if (! $n->read_at)
                            <x-senyum-badge status="pending" class="ml-auto">BARU</x-senyum-badge>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
