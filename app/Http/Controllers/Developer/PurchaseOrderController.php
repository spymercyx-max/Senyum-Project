<?php

namespace App\Http\Controllers\Developer;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        if (! in_array($status, PurchaseOrderStatus::values(), true)) {
            $status = null;
        }

        $fulfillment = $request->query('fulfillment');
        if (! in_array($fulfillment, ['delivery', 'pickup'], true)) {
            $fulfillment = null;
        }

        $counts = ['all' => PurchaseOrder::count()];
        foreach (PurchaseOrderStatus::values() as $s) {
            $counts[$s] = PurchaseOrder::where('status', $s)->count();
        }

        $orders = PurchaseOrder::with(['distributor', 'items'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($fulfillment, fn ($q) => $q->where('fulfillment', $fulfillment))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('developer.po', compact('orders', 'counts', 'status', 'fulfillment'));
    }

    public function show(PurchaseOrder $order): View
    {
        $order->load(['distributor.profile', 'items.product', 'histories.actor']);

        $fulfillment = $order->fulfillment ?? 'delivery';
        $nextStatuses = PurchaseOrderStatus::allowedNext($order->status, $fulfillment);

        $labels = [];
        foreach ($nextStatuses as $s) {
            $labels[$s] = PurchaseOrderStatus::tryFrom($s)?->label() ?? strtoupper($s);
        }

        $currentLabel = PurchaseOrderStatus::tryFrom($order->status)?->label() ?? strtoupper($order->status);

        return view('developer.po-detail', compact('order', 'nextStatuses', 'labels', 'currentLabel', 'fulfillment'));
    }

    public function transition(Request $request, PurchaseOrder $order, PurchaseOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'to' => ['required', 'string', 'in:' . implode(',', PurchaseOrderStatus::values())],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'delivery_note' => ['nullable', 'string', 'max:1000'],
            'pickup_message' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->transition($order, $data['to'], $request->user(), [
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'delivery_note' => $data['delivery_note'] ?? null,
                'pickup_message' => $data['pickup_message'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['to' => $e->getMessage()])->withInput();
        } catch (\Throwable) {
            return back()->withErrors(['to' => 'Gagal memproses purchase order. Silakan coba lagi.'])->withInput();
        }

        $label = PurchaseOrderStatus::tryFrom($data['to'])?->label() ?? strtoupper($data['to']);

        return back()->with('success', "PO {$order->code} berhasil diubah menjadi {$label}.");
    }
}
