<?php

namespace App\Enums;

/**
 * SATU-SATUNYA otoritas status Purchase Order.
 *
 * Alur DELIVERY (dikirim):  DRAFT → DISETUJUI → DIKIRIM → SELESAI
 * Alur PICK-UP (diambil):   DRAFT → DISETUJUI → DIAMBIL → SELESAI
 * Penolakan:                DRAFT → DITOLAK (arsip riwayat, tidak dihapus)
 *
 * Jangan duplikasi peta transisi ini di controller/Blade/Livewire —
 * gunakan PurchaseOrderStatus::allowedNext() dan ::can().
 */
enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Dikirim = 'dikirim';
    case Diambil = 'diambil';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'DRAFT',
            self::Disetujui => 'DISETUJUI',
            self::Ditolak => 'DITOLAK',
            self::Dikirim => 'DIKIRIM',
            self::Diambil => 'DIAMBIL',
            self::Selesai => 'SELESAI',
        };
    }

    public function isFinal(): bool
    {
        return $this === self::Selesai || $this === self::Ditolak;
    }

    /**
     * @return list<string> status tujuan yang sah dari $from untuk fulfillment tsb.
     */
    public static function allowedNext(string $from, string $fulfillment): array
    {
        $isDelivery = $fulfillment === PoFulfillment::Delivery->value;

        return match ($from) {
            self::Draft->value => [self::Disetujui->value, self::Ditolak->value],
            self::Disetujui->value => [$isDelivery ? self::Dikirim->value : self::Diambil->value],
            self::Dikirim->value, self::Diambil->value => [self::Selesai->value],
            default => [],
        };
    }

    public static function can(string $from, string $to, string $fulfillment): bool
    {
        return in_array($to, self::allowedNext($from, $fulfillment), true);
    }

    /** Semua nilai status yang valid (untuk validasi). */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
