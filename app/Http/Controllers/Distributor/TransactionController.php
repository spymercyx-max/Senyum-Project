<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\Outlet;
use App\Models\Product;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TransactionController extends Controller
{
    public function order(Request $request): View
    {
        $user = $request->user();

        return view('distributor.order', [
            'outlets' => $user->outlets()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::active()->with(['tiers', 'inventory'])->orderBy('name')->get(),
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function storeOrder(TransactionRequest $request, TransactionService $service): RedirectResponse
    {
        return $this->handleStore($request, $service, 'distributor.transaksi');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        // Void dibatalkan disembunyikan default; ?status=void menampilkannya.
        $showVoid = $request->query('status') === 'void';
        $base = $user->transactions()->with(['outlet', 'items.product']);
        $transactions = ($showVoid ? $base->withTrashed()->where('status', 'void') : $base)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('distributor.transaksi', [
            'transactions' => $transactions,
            'showVoid' => $showVoid,
            'outlets' => $user->outlets()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::active()->with('inventory')->orderBy('name')->get(),
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function store(TransactionRequest $request, TransactionService $service): RedirectResponse
    {
        return $this->handleStore($request, $service, 'distributor.transaksi');
    }

    public function retail(Request $request): View
    {
        $user = $request->user();

        return view('distributor.jual-ecer', [
            'outlets' => $user->outlets()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::active()->with(['tiers', 'inventory'])->orderBy('name')->get(),
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function storeRetail(Request $request, TransactionService $service): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'outlet_id' => ['required', 'exists:outlets,id'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'buyer_name' => 'nama pembeli',
            'outlet_id' => 'outlet sumber',
            'sold_at' => 'tanggal penjualan',
            'notes' => 'catatan',
            'items' => 'item produk',
            'items.*.product_id' => 'produk',
            'items.*.qty' => 'jumlah',
            'items.*.price' => 'harga',
        ]);

        $outlet = Outlet::findOrFail($data['outlet_id']);
        abort_if((int) $outlet->distributor_id !== (int) $user->id, 403, 'Outlet ini bukan milik Anda.');

        $items = array_map(fn ($row) => [
            'product_id' => (int) $row['product_id'],
            'qty' => (int) $row['qty'],
            'price' => ($row['price'] ?? null) === '' ? null : ($row['price'] ?? null),
        ], $data['items']);

        try {
            $trx = $service->createTransaction($user, $outlet, $items, [
                'type' => 'retail',
                'buyer_name' => $data['buyer_name'] ?? null,
                'sold_at' => $data['sold_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor.jual-ecer')
            ->with('success', "Penjualan eceran {$trx->code} berhasil dicatat (Rp ".number_format($trx->total_amount, 0, ',', '.').'). Langkah berikutnya: catat penjualan berikutnya atau cek pembukuan.');
    }

    public function show(Request $request, int $transaction): View
    {
        $trx = $this->ownedTransaction($request, $transaction);

        return view('distributor.transaksi-detail', [
            'transaction' => $trx,
            'history' => \App\Models\ActivityLog::where('entity', \App\Models\Transaction::class)
                ->where('entity_id', $trx->id)->latest()->get(),
            'territory' => $request->user()->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function edit(Request $request, int $transaction): View
    {
        $trx = $this->ownedTransaction($request, $transaction);
        abort_if($trx->status === 'void', 403, 'Transaksi yang dibatalkan tidak bisa diubah.');

        return view('distributor.transaksi-edit', [
            'transaction' => $trx,
            'outlets' => $request->user()->outlets()->where('status', 'active')->orderBy('name')->get(),
            'products' => Product::active()->with('inventory')->orderBy('name')->get(),
            'territory' => $request->user()->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function update(Request $request, int $transaction, TransactionService $service): RedirectResponse
    {
        $trx = $this->ownedTransaction($request, $transaction);
        abort_if($trx->status === 'void', 403, 'Transaksi yang dibatalkan tidak bisa diubah.');

        $data = $request->validate([
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'correction_reason' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'buyer_name' => 'nama pembeli',
            'sold_at' => 'tanggal penjualan',
            'items.*.product_id' => 'produk',
            'items.*.qty' => 'jumlah',
            'items.*.price' => 'harga',
        ]);

        try {
            $updated = $service->updateTransaction($request->user(), $trx, $data['items'], [
                'buyer_name' => $data['buyer_name'] ?? null,
                'sold_at' => $data['sold_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'reason' => $data['correction_reason'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor.transaksi.show', $updated)
            ->with('success', "Koreksi {$updated->code} tersimpan (total baru Rp " . number_format($updated->total_amount, 0, ',', '.') . "). Stok menyesuaikan selisihnya.");
    }

    public function void(Request $request, int $transaction, TransactionService $service): RedirectResponse
    {
        $trx = $this->ownedTransaction($request, $transaction);

        $reason = trim((string) $request->input('reason', ''));

        try {
            $service->voidTransaction($request->user(), $trx, $reason !== '' ? $reason : null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['void' => $e->getMessage()]);
        }

        return redirect()->route('distributor.transaksi')
            ->with('success', "Transaksi {$trx->code} dibatalkan; stok dikembalikan dan riwayat tersimpan.");
    }

    /** Transaksi milik distributor login (termasuk yang void untuk audit). */
    protected function ownedTransaction(Request $request, int $id): \App\Models\Transaction
    {
        $trx = \App\Models\Transaction::withTrashed()->with(['outlet', 'items.product'])->findOrFail($id);
        abort_if((int) $trx->distributor_id !== (int) $request->user()->id, 403, 'Transaksi ini bukan milik Anda.');

        return $trx;
    }

    protected function handleStore(TransactionRequest $request, TransactionService $service, string $redirect): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $outlet = Outlet::findOrFail($data['outlet_id']);
        abort_if((int) $outlet->distributor_id !== (int) $user->id, 403, 'Outlet ini bukan milik Anda.');

        try {
            $trx = $service->createTransaction($user, $outlet, $data['items'], [
                'type' => 'outlet',
                'buyer_name' => $data['buyer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'sold_at' => $data['sold_at'] ?? now(),
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route($redirect)
            ->with('success', "Pesanan {$trx->code} berhasil dicatat (Rp ".number_format($trx->total_amount, 0, ',', '.').'). Langkah berikutnya: lihat riwayat transaksi atau buat pesanan lagi.');
    }
}
