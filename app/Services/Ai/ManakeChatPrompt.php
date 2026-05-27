<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManakeChatPrompt
{
    public function build(): string
    {
        try {
            $siteName = site_setting('brand.name', 'Manake');
            $tagline = site_setting('brand.tagline', 'Rental Alat Produksi Profesional');
            $owner = 'Kiki Rachmat';

            $categories = schema_table_exists_cached('categories')
                ? Category::query()
                    ->select(['name'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (Category $category) => "- {$category->name}")
                    ->implode("\n")
                : 'Data kategori sedang tidak tersedia.';

            $equipments = schema_table_exists_cached('equipments')
                ? Equipment::with('category:id,name')
                    ->where('status', 'ready')
                    ->orderBy('name')
                    ->limit(20)
                    ->get(['id', 'name', 'price_per_day', 'stock', 'category_id'])
                    ->map(function (Equipment $equipment) {
                        $price = number_format((int) $equipment->price_per_day, 0, ',', '.');

                        return "- {$equipment->name} ({$equipment->category?->name}): Rp{$price}/hari. Stok: {$equipment->stock}";
                    })
                    ->implode("\n")
                : 'Data alat sedang tidak tersedia.';
        } catch (Throwable $e) {
            Log::error('Chatbot prompt building failure: '.$e->getMessage());
            $siteName = 'Manake';
            $tagline = 'Rental Alat Produksi Profesional';
            $owner = 'Kiki Rachmat';
            $categories = 'Data kategori sedang tidak tersedia.';
            $equipments = 'Data alat sedang tidak tersedia.';
        }

        return "Kamu adalah Manake Guide, asisten khusus platform {$siteName} ({$tagline}).

ATURAN WAJIB:
1. Jawab hanya topik Manake: katalog alat, harga, stok, ketersediaan, cara sewa, pembayaran, pickup/return, aturan rental, lokasi, order, akun, dan support.
2. Kalau pertanyaan keluar dari Manake, tolak singkat dan arahkan kembali ke rental/peralatan Manake.
3. Jangan memberi saran medis, hukum, finansial, politik, akademik umum, coding umum, atau topik umum yang tidak terkait Manake.
4. Jangan mengarang data. Jika data tidak ada di konteks, arahkan user cek katalog, availability board, atau kontak Manake.
5. Jawab dalam Bahasa Indonesia, ramah, profesional, dan maksimal 3 kalimat.

KONTEKS SISTEM:
- Owner/Developer: {$owner}
- Flow: pilih alat -> tentukan tanggal -> cart -> checkout -> pembayaran Midtrans -> ambil/return alat.
- Aturan sewa: buffer 1 hari sebelum/sesudah masa sewa untuk QC dan maintenance.

KATEGORI:
{$categories}

ALAT READY:
{$equipments}";
    }
}
