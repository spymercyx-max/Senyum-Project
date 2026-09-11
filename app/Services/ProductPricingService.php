<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceTier;
use InvalidArgumentException;

/**
 * Harga bertingkat produk per channel (customer | distributor).
 * Harga disimpan sebagai integer rupiah. Resolusi deterministik:
 * tier pertama (min_qty terkecil) yang cocok dengan qty.
 *
 * ATURAN KANAL TERTUTUP (§38):
 * - channel distributor: HANYA tier distributor → harga dasar
 *   distributor. TIDAK PERNAH tier/harga customer.
 * - channel customer: HANYA tier customer → harga dasar customer.
 *   TIDAK PERNAH tier/harga distributor.
 * Harga jual Distributor→Outlet BUKAN bagian pricing ini — ia adalah
 * nilai per-transaksi yang diinput distributor (tidak menyentuh master).
 */
class ProductPricingService
{
    public function resolve(Product $product, string $channel, int $qty): int
    {
        $tiers = $product->relationLoaded('tiers')
            ? $product->tiers->where('channel', $channel)->sortBy('min_qty')->values()
            : $product->tiers()->where('channel', $channel)->orderBy('min_qty')->get();

        foreach ($tiers as $tier) {
            /** @var ProductPriceTier $tier */
            if ($tier->matches($qty)) {
                return (int) $tier->price;
            }
        }

        // Fallback HANYA ke harga dasar kanal yang sama — tidak bocor antar kanal.
        if ($channel === ProductPriceTier::CHANNEL_DISTRIBUTOR) {
            return max(0, (int) $product->distributor_price);
        }

        return max(0, (int) $product->price);
    }

    /**
     * Validasi daftar tier dari form. Murni (tanpa DB) agar mudah dites.
     *
     * @param  array<int, array{min_qty:mixed,max_qty:mixed,price:mixed}> $tiers
     * @return array<int, array{min_qty:int,max_qty:?int,price:int}> tier ter-normalisasi & terurut
     *
     * @throws InvalidArgumentException
     */
    public static function validateTiers(array $tiers): array
    {
        $normalized = [];

        foreach (array_values($tiers) as $i => $row) {
            $min = (int) ($row['min_qty'] ?? 0);
            $max = ($row['max_qty'] ?? null) === null || ($row['max_qty'] ?? '') === ''
                ? null
                : (int) $row['max_qty'];
            $price = (int) ($row['price'] ?? -1);
            $n = $i + 1;

            if ($min < 1) {
                throw new InvalidArgumentException("Tingkat #{$n}: jumlah minimum minimal 1.");
            }
            if ($max !== null && $max < $min) {
                throw new InvalidArgumentException("Tingkat #{$n}: jumlah maksimum harus >= minimum.");
            }
            if ($price < 0) {
                throw new InvalidArgumentException("Tingkat #{$n}: harga tidak boleh negatif.");
            }

            $normalized[] = ['min_qty' => $min, 'max_qty' => $max, 'price' => $price];
        }

        usort($normalized, fn ($a, $b) => $a['min_qty'] <=> $b['min_qty']);

        $openEnded = 0;
        foreach ($normalized as $i => $row) {
            if ($row['max_qty'] === null) {
                $openEnded++;
            }
            if ($i > 0) {
                $prev = $normalized[$i - 1];
                $prevMax = $prev['max_qty'] ?? PHP_INT_MAX;
                if ($row['min_qty'] === $prev['min_qty']) {
                    throw new InvalidArgumentException('Rentang duplikat: dua tingkat mulai dari jumlah yang sama.');
                }
                if ($row['min_qty'] <= $prevMax) {
                    throw new InvalidArgumentException('Rentang tumpang tindih: periksa kembali batas tiap tingkat.');
                }
            }
        }

        if ($openEnded > 1) {
            throw new InvalidArgumentException('Hanya satu tingkat yang boleh tanpa batas maksimum (tingkat terakhir).');
        }

        return $normalized;
    }

    /**
     * Sinkronisasi tier satu channel: hapus yang lama, simpan yang baru.
     *
     * @param  array<int, array{min_qty:mixed,max_qty:mixed,price:mixed}> $tiers
     */
    public function syncChannel(Product $product, string $channel, array $tiers): void
    {
        $rows = self::validateTiers($tiers);
        $product->tiers()->where('channel', $channel)->delete();
        foreach ($rows as $row) {
            $product->tiers()->create($row + ['channel' => $channel]);
        }
    }
}
