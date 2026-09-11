<div>
    <label class="sn-label" for="outlet-picker-url">Link Google Maps (wajib)</label>
    <input id="outlet-picker-url" type="url" name="google_maps_url" wire:model.blur="url"
        class="sn-input @error('google_maps_url') sn-input--error @enderror"
        placeholder="https://maps.google.com/... atau https://maps.app.goo.gl/..."
        required maxlength="2000">
    <p class="sn-help">Buka outlet di Google Maps → Bagikan → Salin link → tempel di sini. Koordinat dibaca otomatis.</p>
    @if ($status === 'ok')
        <p class="mt-2 font-bold text-sm text-black">LOKASI TERBACA ✓</p>
        <p class="font-mono text-sm mt-1">{{ $lat }}, {{ $lng }}</p>
        <div class="mt-2 border-2 border-black rounded-sm overflow-hidden bg-white">
            <iframe title="Pratinjau lokasi outlet" src="https://www.google.com/maps?q={{ $lat }},{{ $lng }}&output=embed" class="w-full" height="220" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    @elseif ($status === 'error')
        <p class="sn-error mt-2">{{ $message }}</p>
    @endif
</div>
