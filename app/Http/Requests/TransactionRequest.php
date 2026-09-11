<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'outlet_id' => ['required', 'exists:outlets,id'],
            'buyer_name' => ['nullable', 'string', 'max:255'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'outlet_id' => 'outlet',
            'buyer_name' => 'nama pembeli',
            'sold_at' => 'tanggal penjualan',
            'notes' => 'catatan',
            'items' => 'item produk',
            'items.*.product_id' => 'produk',
            'items.*.qty' => 'jumlah',
            'items.*.price' => 'harga',
        ];
    }
}
