<div>
    @if ($notice)
        <x-senyum-alert type="success" :message="$notice" class="mb-4" />
    @endif
    @if ($error)
        <x-senyum-alert type="danger" :message="$error" class="mb-4" />
    @endif

    <div class="flex flex-wrap gap-2 mb-4" role="group" aria-label="Filter status stok">
        @php($tabs = ['' => 'Semua (' . $counts['all'] . ')', 'healthy' => 'Sehat (' . $counts['healthy'] . ')', 'low' => 'Rendah (' . $counts['low'] . ')', 'critical' => 'Kritis (' . $counts['critical'] . ')', 'out' => 'Habis (' . $counts['out'] . ')'])
        @foreach ($tabs as $value => $label)
            <button type="button" wire:click="$set('status', '{{ $value }}')" class="sn-btn sn-btn-sm {{ $status === $value ? 'sn-btn-primary' : 'sn-btn-ghost' }}">{{ $label }}</button>
        @endforeach
    </div>

    @if ($inventories->isEmpty())
        <x-senyum-empty-state title="Tidak ada inventory" message="Belum ada data stok pada filter ini." />
    @else
        @php($badgeMap = ['healthy' => 'active', 'low' => 'pending', 'critical' => 'warning', 'out' => 'danger'])
        <x-senyum-data-table :headers="['Produk', 'Stok', 'Cadangan', 'Tersedia', 'Status', 'Aksi']">
            @foreach ($inventories as $inv)
                <tr>
                    <td>
                        <p class="font-bold">{{ $inv->product?->name ?? '—' }}</p>
                        <p class="font-mono text-xs text-neutral-500">{{ $inv->product?->sku ?? '' }}</p>
                    </td>
                    <td class="sn-num">{{ $inv->stock }}</td>
                    <td class="sn-num">{{ $inv->reserved }}</td>
                    <td class="sn-num"><strong>{{ $inv->available }}</strong> / {{ $inv->threshold }}</td>
                    <td><x-senyum-badge status="{{ $badgeMap[$inv->stock_status] ?? 'draft' }}">{{ strtoupper($inv->stock_status) }}</x-senyum-badge></td>
                    <td>
                        <button type="button" wire:click="openAdjust({{ $inv->id }})" class="sn-btn sn-btn-yellow sn-btn-sm">Sesuaikan</button>
                    </td>
                </tr>
            @endforeach
        </x-senyum-data-table>

        <div class="block md:hidden space-y-3 mt-4">
            @foreach ($inventories as $inv)
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-display font-bold uppercase text-sm">{{ $inv->product?->name ?? '—' }}</p>
                        <x-senyum-badge status="{{ $badgeMap[$inv->stock_status] ?? 'draft' }}">{{ strtoupper($inv->stock_status) }}</x-senyum-badge>
                    </div>
                    <p class="text-sm mt-1">Stok <strong>{{ $inv->stock }}</strong> · Cadangan {{ $inv->reserved }} · Tersedia <strong>{{ $inv->available }}</strong></p>
                    <button type="button" wire:click="openAdjust({{ $inv->id }})" class="sn-btn sn-btn-yellow sn-btn-sm mt-3">Sesuaikan</button>
                </div>
            @endforeach
        </div>
    @endif

    @if ($adjustId)
        <div class="sn-modal-backdrop is-open" role="dialog" aria-modal="true" aria-label="Sesuaikan stok">
            <div class="sn-modal">
                <h3 class="sn-modal-title">Sesuaikan stok</h3>
                <form wire:submit="saveAdjust" class="space-y-3">
                    <div>
                        <label class="sn-label" for="inv-delta">Selisih (bisa negatif)</label>
                        <input id="inv-delta" type="number" wire:model="delta" class="sn-input" placeholder="Contoh: 10 atau -5" required>
                        @error('delta') <p class="sn-error">{{ $message }}</p> @enderror
                        <p class="sn-help">Angka positif menambah stok, negatif mengurangi stok.</p>
                    </div>
                    <div>
                        <label class="sn-label" for="inv-reason">Alasan (opsional)</label>
                        <input id="inv-reason" type="text" wire:model="reason" class="sn-input" placeholder="Contoh: stok opname" maxlength="500">
                        @error('reason') <p class="sn-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" wire:click="cancelAdjust" class="sn-btn sn-btn-ghost sn-btn-sm">Batal</button>
                        <button type="submit" class="sn-btn sn-btn-primary sn-btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
