<?php

namespace App\Http\Requests;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fulfillment' => ['required', 'in:delivery,pickup'],
            // Tanggal bisnis bebas: hari ini, lampau, maupun mendatang.
            'order_date' => ['required', 'date', 'after:1999-12-31'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
            'payment_proof' => array_merge(
                ['required_if:fulfillment,delivery', 'file'],
                ImageUpload::rules(false)
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'fulfillment' => 'mode pemenuhan',
            'order_date' => 'tanggal pemesanan',
            'notes' => 'catatan',
            'items' => 'item produk',
            'items.*.product_id' => 'produk',
            'items.*.qty' => 'jumlah',
            'items.*.price' => 'harga',
            'payment_proof' => 'bukti pembayaran',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_proof.required_if' => 'Bukti pembayaran wajib diunggah untuk PO DIKIRIM.',
        ];
    }
}
