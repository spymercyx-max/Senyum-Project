<?php

namespace App\Http\Requests;

use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isDeveloper();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $productId],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $productId],
            // Harga Customer (dasar) & Harga Distributor — keduanya wajib.
            'price' => ['required', 'integer', 'min:0'],
            'distributor_price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,active,archived'],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            // Sampul: file opsional (aturan terpusat ImageUpload).
            'image' => ImageUpload::rules(false),
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'availability_note' => ['nullable', 'string', 'max:255'],
            // Tier harga dinamis per channel; validasi bisnis via ProductPricingService::validateTiers().
            'tiers_customer' => ['nullable', 'array'],
            'tiers_customer.*.min_qty' => ['nullable', 'integer', 'min:1'],
            'tiers_customer.*.max_qty' => ['nullable', 'integer', 'min:1'],
            'tiers_customer.*.price' => ['nullable', 'integer', 'min:0'],
            'tiers_distributor' => ['nullable', 'array'],
            'tiers_distributor.*.min_qty' => ['nullable', 'integer', 'min:1'],
            'tiers_distributor.*.max_qty' => ['nullable', 'integer', 'min:1'],
            'tiers_distributor.*.price' => ['nullable', 'integer', 'min:0'],
            // Galeri tambahan (dikelola ProductImageController; diterima di store/update bila ada).
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ImageUpload::rules(false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'price' => 'Harga Customer',
            'distributor_price' => 'Harga Distributor',
            'image' => 'Gambar sampul',
            'tiers_customer' => 'Tingkat harga customer',
            'tiers_distributor' => 'Tingkat harga distributor',
        ];
    }
}
