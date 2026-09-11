@extends('layouts.developer')

@section('title', $order->code . ' — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('developer.po') }}" class="sn-btn sn-btn-ghost sn-btn-sm mb-3">← Semua PO</a>
        <p class="sn-kicker">Operations · Detail PO</p>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl font-mono">{{ $order->code }}</h1>
            <span class="sn-badge sn-badge--{{ $order->status }}">{{ $currentLabel }}</span>
            <span class="sn-badge">{{ $fulfillment === 'pickup' ? 'DIAMBIL' : 'DIKIRIM' }}</span>
        </div>
        <p class="text-sm text-neutral-600 mt-1">Distributor: <strong>{{ $order->distributor?->name ?? '—' }}</strong> · Total: <strong>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>
        <p class="text-sm text-neutral-600 mt-1">Tanggal pesan: <strong>{{ $order->order_date?->translatedFormat('d M Y') ?? '—' }}</strong></p>
        @if ($order->notes)
            <p class="text-sm text-neutral-600 mt-1">Catatan: {{ $order->notes }}</p>
        @endif
        @if ($order->payment_proof)
            <p class="text-sm mt-2"><a href="{{ asset('storage/' . ltrim($order->payment_proof, '/')) }}" target="_blank" rel="noopener" class="sn-btn sn-btn-ghost sn-btn-sm">Lihat bukti pembayaran</a></p>
        @endif
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
        <x-senyum-card title="Item pesanan" kicker="Items + snapshot">
            @if ($order->items->isEmpty())
                <x-senyum-empty-state title="Tidak ada item" message="PO ini tidak memiliki item." />
            @else
                <div class="sn-table-wrap">
                    <table class="sn-table">
                        <thead>
                            <tr>
                                <th>Produk (snapshot)</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <p class="font-bold">{{ $item->product_name ?? $item->product?->name ?? '—' }}</p>
                                        <p class="font-mono text-xs text-neutral-500">{{ $item->sku ?? $item->product?->sku ?? '' }}</p>
                                    </td>
                                    <td class="sn-num">{{ $item->qty }}</td>
                                    <td class="sn-num">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="sn-num">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="sn-table-note">Nama & SKU diambil dari snapshot saat PO dibuat.</p>
            @endif
        </x-senyum-card>

        <div class="space-y-6">
            <x-senyum-card title="Status pengiriman" kicker="Fulfillment">
                <dl class="text-sm space-y-2">
                    <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-32 shrink-0 text-neutral-500">Mode</dt><dd class="font-bold">{{ $fulfillment === 'pickup' ? 'DIAMBIL (pickup)' : 'DIKIRIM (delivery)' }}</dd></div>
                    @if ($order->tracking_number)
                        <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-32 shrink-0 text-neutral-500">No. resi</dt><dd class="font-mono font-bold">{{ $order->tracking_number }}</dd></div>
                    @endif
                    @if ($order->delivery_note)
                        <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-32 shrink-0 text-neutral-500">Catatan kirim</dt><dd>{{ $order->delivery_note }}</dd></div>
                    @endif
                    @if ($order->pickup_message)
                        <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-32 shrink-0 text-neutral-500">Pesan ambil</dt><dd>{{ $order->pickup_message }}</dd></div>
                    @endif
                    @if ($order->rejection_reason)
                        <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-32 shrink-0 text-neutral-500">Alasan tolak</dt><dd>{{ $order->rejection_reason }}</dd></div>
                    @endif
                </dl>
            </x-senyum-card>

            <x-senyum-card title="Aksi" kicker="Transition">
                @if (empty($nextStatuses))
                    <x-senyum-alert type="info" :message="'PO ini sudah final (' . $currentLabel . ') dan tidak bisa diubah lagi.'" />
                @else
                    {{-- DRAFT → SETUJUI / TOLAK --}}
                    @if (in_array('disetujui', $nextStatuses, true))
                        <div class="flex flex-wrap gap-2 mb-4">
                            <form method="POST" action="{{ route('developer.po.transition', $order) }}" onsubmit="return confirm('SETUJUI PO {{ $order->code }}? Stok akan dialokasikan.');">
                                @csrf
                                <input type="hidden" name="to" value="disetujui">
                                <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm">SETUJUI</button>
                            </form>
                            <button type="button" data-modal-open="#modal-po-reject" class="sn-btn sn-btn-danger sn-btn-sm">TOLAK</button>
                        </div>
                        <x-senyum-modal id="modal-po-reject" title="TOLAK PO?" confirm="Ya, tolak">
                            <p>PO <strong class="font-mono">{{ $order->code }}</strong> akan ditolak. Alasan wajib diisi.</p>
                            <form method="POST" action="{{ route('developer.po.transition', $order) }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="to" value="ditolak">
                                <div>
                                    <label class="sn-label" for="po-reject-reason">Alasan penolakan *</label>
                                    <textarea id="po-reject-reason" name="rejection_reason" rows="3" class="sn-textarea" maxlength="1000" required placeholder="Contoh: stok tidak mencukupi">{{ old('rejection_reason') }}</textarea>
                                </div>
                            </form>
                        </x-senyum-modal>
                    @endif

                    {{-- DISETUJUI + delivery → DIKIRIM (form resi + catatan) --}}
                    @if (in_array('dikirim', $nextStatuses, true))
                        <form method="POST" action="{{ route('developer.po.transition', $order) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="to" value="dikirim">
                            <div>
                                <label class="sn-label" for="po-resi">Nomor resi *</label>
                                <input id="po-resi" name="tracking_number" type="text" value="{{ old('tracking_number', $order->tracking_number) }}" class="sn-input" maxlength="255" required placeholder="Contoh: JNE123456789">
                            </div>
                            <div>
                                <label class="sn-label" for="po-delivery-note">Catatan pengiriman (opsional)</label>
                                <textarea id="po-delivery-note" name="delivery_note" rows="2" class="sn-textarea" maxlength="1000" placeholder="Contoh: estimasi 2 hari">{{ old('delivery_note', $order->delivery_note) }}</textarea>
                            </div>
                            <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm" onclick="return confirm('Kirim PO {{ $order->code }} dengan resi ini?');">KIRIM (DIKIRIM)</button>
                        </form>
                    @endif

                    {{-- DISETUJUI + pickup → DIAMBIL (form pesan) --}}
                    @if (in_array('diambil', $nextStatuses, true))
                        <form method="POST" action="{{ route('developer.po.transition', $order) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="to" value="diambil">
                            <div>
                                <label class="sn-label" for="po-pickup-msg">Pesan pengambilan (opsional)</label>
                                <textarea id="po-pickup-msg" name="pickup_message" rows="2" class="sn-textarea" maxlength="1000" placeholder="Contoh: ambil jam 09.00–15.00, bawa nota">{{ old('pickup_message', $order->pickup_message) }}</textarea>
                            </div>
                            <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm" onclick="return confirm('Tandai PO {{ $order->code }} SIAP DIAMBIL?');">SIAP DIAMBIL</button>
                        </form>
                    @endif

                    {{-- DIKIRIM / DIAMBIL → SELESAI --}}
                    @if (in_array('selesai', $nextStatuses, true))
                        <form method="POST" action="{{ route('developer.po.transition', $order) }}" onsubmit="return confirm('Selesaikan PO {{ $order->code }}?');">
                            @csrf
                            <input type="hidden" name="to" value="selesai">
                            <button type="submit" class="sn-btn sn-btn-primary sn-btn-sm">SELESAI</button>
                        </form>
                    @endif

                    <p class="sn-help mt-3">Aksi yang tampil mengikuti alur resmi: {{ implode(', ', array_map(fn ($s) => $labels[$s] ?? strtoupper($s), $nextStatuses)) }}.</p>
                @endif
            </x-senyum-card>

            <x-senyum-card title="Linimasa" kicker="Timeline">
                @if ($order->histories->isEmpty())
                    <x-senyum-empty-state title="Belum ada riwayat" message="Perubahan status akan tercatat di sini." />
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($order->histories as $h)
                            <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                                <p><strong>{{ $h->actor?->name ?? 'Sistem' }}</strong> · <span class="font-mono text-xs font-bold">{{ strtoupper($h->from ?? '—') }} → {{ strtoupper($h->to) }}</span></p>
                                @if ($h->note)
                                    <p class="text-xs">{{ $h->note }}</p>
                                @endif
                                <p class="font-mono text-xs text-neutral-500">{{ $h->created_at?->translatedFormat('d M Y H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-senyum-card>
        </div>
    </div>
</div>
@endsection
