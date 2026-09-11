<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Support\Senyum;

class WhatsAppService
{
    public function orderNumber(): string
    {
        return (string) (Setting::get('whatsapp_number', config('senyum.whatsapp_number')) ?? config('senyum.whatsapp_number'));
    }

    public function developerNumber(): string
    {
        return (string) (Setting::get('developer_whatsapp', config('senyum.developer_whatsapp')) ?? config('senyum.developer_whatsapp'));
    }

    public function waUrl(string $phone, string $message): string
    {
        $digits = (string) preg_replace('/\D+/', '', $phone);
        $digits = ltrim($digits, '+');

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return 'https://wa.me/' . $digits . '?text=' . urlencode($message);
    }

    public function productInquiryUrl(Product $product, ?string $customMessage = null): string
    {
        $message = $customMessage ?? sprintf(
            "Halo %s! Saya tertarik dengan produk %s (%s). Apakah masih tersedia? Terima kasih.",
            config('senyum.brand', 'SENYUM'),
            $product->name,
            Senyum::formatRupiah((int) $product->price)
        );

        return $this->waUrl($this->orderNumber(), $message);
    }

    public function partnershipUrl(?string $message = null): string
    {
        $message ??= sprintf(
            "Halo %s! Saya ingin bermitra menjadi distributor di wilayah saya. Mohon info pendaftarannya. Terima kasih.",
            config('senyum.brand', 'SENYUM')
        );

        return $this->waUrl($this->developerNumber(), $message);
    }
}
