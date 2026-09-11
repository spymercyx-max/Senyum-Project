@props(['id' => 'sn-modal', 'title' => 'Konfirmasi', 'confirm' => 'Ya, lanjutkan'])

<div id="{{ $id }}" class="sn-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" {{ $attributes }}>
    <div class="sn-modal">
        <h3 id="{{ $id }}-title" class="sn-modal-title">{{ $title }}</h3>
        <div class="text-sm leading-relaxed">
            {{ $slot }}
        </div>
        <div class="flex gap-2 justify-end mt-6">
            <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm" data-modal-close>Batal</button>
            <button type="button" class="sn-btn sn-btn-danger sn-btn-sm" data-modal-confirm>{{ $confirm }}</button>
        </div>
    </div>
</div>
