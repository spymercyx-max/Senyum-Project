<?php

namespace App\Services\Contracts;

/**
 * Abstraksi provider peta. Seluruh logika bisnis bergantung pada
 * MapService/interface ini — jangan sebar panggilan API Google
 * langsung di controller.
 */
interface MapProviderInterface
{
    public function name(): string;

    /**
     * Resolusi short link (mis. https://maps.app.goo.gl/xxxx) menjadi
     * URL kanonis final (mengikuti redirect). Hasil sebaiknya di-cache
     * oleh pemanggil.
     *
     * @throws \App\Services\UnresolvableMapsLinkException
     */
    public function resolveShortUrl(string $url): string;

    /** Apakah API key yang dibutuhkan sudah dikonfigurasi. */
    public function isConfigured(): bool;
}
