<div class="sn-card">
    @if ($success)
        <div class="mb-4"><x-senyum-alert type="success" :message="$success" /></div>
    @endif

    <form wire:submit="submit">
        <div class="grid gap-4">
            <div>
                <label class="sn-label" for="of-outlet">Outlet yang membeli</label>
                <select id="of-outlet" wire:model="outlet_id" class="sn-select @error('outlet_id') sn-select--error @enderror">
                    <option value="">— Pilih outlet —</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                    @endforeach
                </select>
                <p class="sn-help">Hanya outlet milikmu yang muncul di sini.</p>
                @error('outlet_id')<p class="sn-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="sn-label" for="of-date">Tanggal penjualan</label>
                <input id="of-date" type="date" wire:model="sold_at" class="sn-input @error('sold_at') sn-input--error @enderror">
                <p class="sn-help">Tanggal bisnis transaksi (bukan waktu pencatatan).</p>
                @error('sold_at')<p class="sn-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <span class="sn-label">Produk, jumlah & harga ke outlet</span>
                <div class="grid gap-3">
                    @foreach ($rows as $i => $row)
                        <div wire:key="row-{{ $i }}" class="border-2 border-black rounded-sm p-2 bg-white">
                            <div class="grid grid-cols-[1fr_70px_110px_auto] gap-2 items-start">
                                <div>
                                    <select wire:model.live="rows.{{ $i }}.product_id" class="sn-select @error('rows.' . $i . '.product_id') sn-select--error @enderror" aria-label="Produk baris {{ $i + 1 }}">
                                        <option value="">— Pilih produk —</option>
                                        @foreach ($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} — Rp {{ number_format($distPrices[$p->id] ?? $p->distributor_price ?? $p->price, 0, ',', '.') }}</option>
                                        @endforeach
                                    </select>
                                    @error('rows.' . $i . '.product_id')<p class="sn-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input type="number" wire:model.live="rows.{{ $i }}.qty" class="sn-input @error('rows.' . $i . '.qty') sn-input--error @enderror" min="1" aria-label="Jumlah baris {{ $i + 1 }}">
                                    @error('rows.' . $i . '.qty')<p class="sn-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input type="number" wire:model.live="rows.{{ $i }}.price" class="sn-input @error('rows.' . $i . '.price') sn-input--error @enderror" min="0" placeholder="Harga" aria-label="Harga ke outlet baris {{ $i + 1 }}">
                                    @error('rows.' . $i . '.price')<p class="sn-error">{{ $message }}</p>@enderror
                                </div>
                                <button type="button" wire:click="removeRow({{ $i }})" class="sn-btn sn-btn-ghost sn-btn-sm" aria-label="Hapus baris {{ $i + 1 }}">✕</button>
                            </div>
                            <p class="text-xs text-right mt-1">Subtotal: <strong>Rp {{ number_format($rowSubtotals[$i] ?? 0, 0, ',', '.') }}</strong></p>
                        </div>
                    @endforeach
                </div>
                @error('rows')<p class="sn-error">{{ $message }}</p>@enderror
                <button type="button" wire:click="addRow" class="sn-btn sn-btn-ghost sn-btn-sm mt-2 w-full md:w-auto">+ Tambah produk</button>
            </div>

            <div>
                <label class="sn-label" for="of-notes">Catatan (opsional)</label>
                <textarea id="of-notes" wire:model="notes" class="sn-textarea" rows="2" placeholder="Contoh: bayar tempo 3 hari"></textarea>
            </div>

            <div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3">
                <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
                <strong class="font-display text-xl">Rp {{ number_format($total, 0, ',', '.') }}</strong>
            </div>

            <button type="submit" class="sn-btn sn-btn-yellow sn-btn-lg w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>Kirim Pesanan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
