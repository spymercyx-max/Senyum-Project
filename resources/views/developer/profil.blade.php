@extends('layouts.developer')

@section('title', 'Profil — SENYUM Command Center')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <p class="sn-kicker">System · Profil</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Profil Developer</h1>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <x-senyum-card title="{{ $user->name }}" kicker="Developer account">
        <dl class="text-sm space-y-2">
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Username</dt><dd class="font-mono">{{ '@' . $user->username }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Email</dt><dd>{{ $user->email ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Telepon</dt><dd class="font-mono">{{ $user->phone ?? '—' }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Kota</dt><dd>{{ $user->profile?->city ?? '—' }} {{ $user->profile?->district ? '· ' . $user->profile->district : '' }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-28 shrink-0 text-neutral-500">Aksi tercatat</dt><dd class="font-mono">{{ $activityCount }} aktivitas</dd></div>
        </dl>
    </x-senyum-card>

    <x-senyum-card title="EDIT profil" kicker="Edit profile">
        <livewire:developer.profile-form />
    </x-senyum-card>

    <x-senyum-card title="Aktivitas saya" kicker="My activity">
        @if ($recentActivity->isEmpty())
            <x-senyum-empty-state title="Belum ada aktivitas" message="Tindakan Anda akan tercatat di sini." />
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($recentActivity as $log)
                    <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                        <p><span class="font-mono text-xs font-bold">{{ $log->action }}</span></p>
                        <p class="font-mono text-xs text-neutral-500">{{ $log->created_at?->translatedFormat('d M Y H:i') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-senyum-card>
</div>
@endsection
