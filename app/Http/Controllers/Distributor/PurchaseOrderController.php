<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = trim((string) $request->query('status', ''));

        $orders = $user->purchaseOrders()->with('items.product')
            ->when($status !== '' && in_array($status, PurchaseOrderStatus::values(), true),
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('distributor.po', [
            'orders' => $orders,
            'products' => Product::active()->with(['tiers', 'inventory'])->orderBy('name')->get(),
            'territory' => $user->distributorProfile?->territory?->displayName(),
            'filterStatus' => $status,
            'statuses' => PurchaseOrderStatus::values(),
            'fulfillmentDefault' => 'delivery',
            'today' => today()->toDateString(),
        ]);
    }

    public function store(PurchaseOrderRequest $request, PurchaseOrderService $service): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = ImageUpload::store($request->file('payment_proof'), 'payment-proofs');
        }

        try {
            $order = $service->createOrder($user, $data['items'], [
                'fulfillment' => $data['fulfillment'],
                'order_date' => $data['order_date'],
                'payment_proof' => $paymentProofPath,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            if ($paymentProofPath) {
                ImageUpload::delete($paymentProofPath);
            }

            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('distributor.po.show', $order)
            ->with('success', "PO {$order->code} berhasil dikirim dan menunggu diproses developer. Langkah berikutnya: pantau statusnya di halaman ini.");
    }

    public function show(Request $request, PurchaseOrder $order): View
    {
        abort_if((int) $order->distributor_id !== (int) $request->user()->id, 403, 'PO ini bukan milik Anda.');

        $order->loadMissing(['items.product', 'histories.actor']);

        $allowedNext = PurchaseOrderStatus::allowedNext($order->status, $order->fulfillment ?? 'delivery');

        return view('distributor.po-detail', [
            'order' => $order,
            'histories' => $order->histories,
            'allowedNext' => $allowedNext,
            'territory' => $request->user()->distributorProfile?->territory?->displayName(),
        ]);
    }
}
