<?php

namespace App\Services;

class PricingService
{
    // ======================================================================
    // APA YANG SAYA LIHAT?
    // -> [LAYANAN HITUNG HARGA & DENDA (PRICING ENGINE)]
    // Kelas ini bertanggung jawab atas semua kalkulasi keuangan, termasuk pajak dan denda keterlambatan pengembalian.
    //
    // 🎓 KEMUNGKINAN PERTANYAAN DOSEN:
    // 1. "Bagaimana Anda menghitung PPN untuk setiap transaksi sewa?"
    // 2. "Bagaimana logika penghitungan denda sewa jika penyewa terlambat mengembalikan?"
    //
    // 🟢 APA YANG BISA SAYA UBAH? (Aman & Mudah)
    // - Nilai Pajak (`TAX_RATE`): Ubah `0.11` (PPN 11%) ke nilai lain (misal `0.12` untuk PPN 12%).
    // - Aturan Denda (`lateFeeFromLateHours` di baris 57): Anda bisa mengubah persentase denda keterlambatan (30%, 50%, 100%) berdasarkan jam keterlambatan sewa.
    // ======================================================================

    public const TAX_RATE = 0.11;

    /**
     * @param  iterable<array{price?:int|float|string,qty?:int|float|string,days?:int|float|string,subtotal?:int|float|string}>  $items
     * @return array{subtotal:int,tax:int,total:int}
     */
    public function calculateOrderTotals(iterable $items, ?int $forcedSubtotal = null): array
    {
        $subtotal = $forcedSubtotal ?? 0;

        if ($forcedSubtotal === null) {
            foreach ($items as $item) {
                $subtotal += $this->lineSubtotal($item);
            }
        }

        $subtotal = max((int) $subtotal, 0);
        $tax = (int) round($subtotal * self::TAX_RATE);

        return [
            'subtotal' => $subtotal,
            'tax' => max($tax, 0),
            'total' => max($subtotal + $tax, 0),
        ];
    }

    /**
     * @param  array{price?:int|float|string,qty?:int|float|string,days?:int|float|string,subtotal?:int|float|string}  $item
     */
    public function lineSubtotal(array $item): int
    {
        if (array_key_exists('subtotal', $item)) {
            return max((int) $item['subtotal'], 0);
        }

        $price = max((int) ($item['price'] ?? 0), 0);
        $qty = max((int) ($item['qty'] ?? 1), 1);
        $days = max((int) ($item['days'] ?? 1), 1);

        return $price * $qty * $days;
    }

    /**
     * Late fee policy:
     * - >3h: 30%
     * - >6h: 50%
     * - >9h: 100%
     *
     * @return array{hours:int,rate:float,amount:int}
     */
    public function lateFeeFromLateHours(int $baseSubtotal, int $lateHours): array
    {
        $rate = 0.0;

        if ($lateHours > 9) {
            $rate = 1.0;
        } elseif ($lateHours > 6) {
            $rate = 0.5;
        } elseif ($lateHours > 3) {
            $rate = 0.3;
        }

        return [
            'hours' => max($lateHours, 0),
            'rate' => $rate,
            'amount' => (int) round(max($baseSubtotal, 0) * $rate),
        ];
    }
}
