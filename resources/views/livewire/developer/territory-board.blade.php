<div>
    @if ($notice)
        <x-senyum-alert type="success" :message="$notice" class="mb-4" />
    @endif
    @if ($error)
        <x-senyum-alert type="danger" :message="$error" class="mb-4" />
    @endif

    @if (! $territoryId)
        <div class="mb-4">
            <label class="sn-label" for="tb-search">Cari wilayah</label>
            <input id="tb-search" type="search" wire:model.live="search" class="sn-input" placeholder="Kota, kecamatan, atau kode…" autocomplete="off">
        </div>

        @if ($territories->isEmpty())
            <x-senyum-empty-state title="Wilayah tidak ditemukan" message="Coba ubah kata kunci pencarian." />
        @else
            <x-senyum-data-table :headers="['Wilayah', 'Kode', 'Distributor', 'Outlet', 'Status']">
                @foreach ($territories as $t)
                    <tr>
                        <td>
                            <p class="font-bold">{{ $t->city }} – {{ $t->district }}</p>
                        </td>
                        <td class="font-mono text-xs">{{ $t->code ?? '—' }}</td>
                        <td class="sn-num">{{ $t->distributor_profiles_count }}</td>
                        <td class="sn-num">{{ $t->outlets_count }}</td>
                        <td><x-senyum-badge status="{{ $t->status === 'active' ? 'active' : 'suspended' }}">{{ strtoupper($t->status) }}</x-senyum-badge></td>
                    </tr>
                @endforeach
            </x-senyum-data-table>
        @endif
    @endif

    <x-senyum-card title="Tempatkan distributor ke wilayah" kicker="Assign territory" class="mt-6">
        <form wire:submit="assign" class="grid sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="sn-label" for="tb-territory">Wilayah</label>
                <select id="tb-territory" wire:model="assignTerritoryId" class="sn-select" @if($territoryId) disabled @endif>
                    <option value="">— Pilih wilayah —</option>
                    @foreach ($allTerritories as $t)
                        <option value="{{ $t->id }}">{{ $t->city }} – {{ $t->district }}</option>
                    @endforeach
                </select>
                @error('assignTerritoryId') <p class="sn-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="sn-label" for="tb-distributor">Distributor (disetujui)</label>
                <select id="tb-distributor" wire:model="distributorId" class="sn-select">
                    <option value="">— Pilih distributor —</option>
                    @foreach ($distributors as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->distributorProfile?->territory?->city ?? 'tanpa wilayah' }})</option>
                    @endforeach
                </select>
                @error('distributorId') <p class="sn-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <button type="submit" class="sn-btn sn-btn-primary sn-btn-sm sn-btn-block">Tempatkan</button>
            </div>
        </form>
        <p class="sn-help mt-2">Distributor yang sudah punya wilayah akan dipindahkan ke wilayah baru.</p>
    </x-senyum-card>
</div>
