<div>
    @if ($notice)
        <x-senyum-alert type="success" :message="$notice" class="mb-4" />
    @endif
    @if ($error)
        <x-senyum-alert type="danger" :message="$error" class="mb-4" />
    @endif

    @php($current = $user->distributorStatus())

    <div class="flex flex-wrap items-center gap-2">
        <x-senyum-badge status="{{ $current ?? 'pending' }}">{{ strtoupper($current ?? 'pending') }}</x-senyum-badge>
        <div class="flex flex-wrap gap-2 ml-auto">
            <button type="button" wire:click="confirm('approve')" class="sn-btn sn-btn-primary sn-btn-sm">Setujui</button>
            <button type="button" wire:click="confirm('reject')" class="sn-btn sn-btn-yellow sn-btn-sm">Tolak</button>
            <button type="button" wire:click="confirm('suspend')" class="sn-btn sn-btn-danger sn-btn-sm">Tangguhkan</button>
        </div>
    </div>

    @if ($confirming)
        <div class="sn-modal-backdrop is-open" role="dialog" aria-modal="true" aria-label="Konfirmasi persetujuan distributor">
            <div class="sn-modal">
                <h3 class="sn-modal-title">
                    {{ $confirming === 'approve' ? 'Setujui distributor?' : ($confirming === 'reject' ? 'Tolak pengajuan?' : 'Tangguhkan distributor?') }}
                </h3>
                <p class="text-sm leading-relaxed mb-4">
                    @if ($confirming === 'approve')
                        Distributor <strong>{{ $user->name }}</strong> akan disetujui dan bisa mulai berjualan.
                    @elseif ($confirming === 'reject')
                        Pengajuan <strong>{{ $user->name }}</strong> akan ditolak. Tulis alasan agar mudah dipahami.
                    @else
                        Akun <strong>{{ $user->name }}</strong> akan ditangguhkan sementara. Tulis alasan penangguhan.
                    @endif
                </p>
                @if (in_array($confirming, ['reject', 'suspend'], true))
                    <label class="sn-label" for="approval-reason">Alasan (opsional)</label>
                    <textarea id="approval-reason" wire:model="reason" rows="3" class="sn-textarea" placeholder="Contoh: data wilayah belum lengkap"></textarea>
                    @error('reason') <p class="sn-error">{{ $message }}</p> @enderror
                @endif
                <div class="flex gap-2 justify-end mt-6">
                    <button type="button" wire:click="cancel" class="sn-btn sn-btn-ghost sn-btn-sm">Batal</button>
                    @if ($confirming === 'approve')
                        <button type="button" wire:click="approve" class="sn-btn sn-btn-primary sn-btn-sm">Ya, setujui</button>
                    @elseif ($confirming === 'reject')
                        <button type="button" wire:click="reject" class="sn-btn sn-btn-yellow sn-btn-sm">Ya, tolak</button>
                    @else
                        <button type="button" wire:click="suspend" class="sn-btn sn-btn-danger sn-btn-sm">Ya, tangguhkan</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
