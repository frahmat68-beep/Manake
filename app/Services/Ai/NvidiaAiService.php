<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NvidiaAiService
{
    protected string $primaryKey;
    protected string $secondaryKey;
    protected string $model;
    protected string $baseUrl = 'https://integrate.api.nvidia.com/v1';

    public function __construct()
    {
        $this->primaryKey = config('services.nvidia.api_key');
        $this->secondaryKey = config('services.nvidia.api_key_secondary');
        $this->model = config('services.nvidia.model', 'meta/llama-3.1-8b-instruct');
    }

    /**
     * Send a message to the AI and get a response.
     */
    public function chat(array $messages)
    {
        if (!collect($messages)->contains('role', 'system')) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $this->buildSystemPrompt(),
            ]);
        }

        // Check if API keys are configured
        if (!$this->primaryKey) {
            Log::error('Nvidia AI Primary API Key is not configured.');
            return "Maaf, sistem AI sedang dalam pemeliharaan (API Key belum dikonfigurasi). Silakan coba lagi nanti.";
        }

        // Try primary key first
        $response = $this->sendRequest($this->primaryKey, $messages);

        // If primary fails (rate limit or auth error), try secondary key
        if ($response->failed() && ($response->status() === 429 || $response->status() === 401)) {
            Log::warning('Nvidia Primary API Key failed, trying secondary key.', [
                'status' => $response->status(),
            ]);
            
            if ($this->secondaryKey) {
                $response = $this->sendRequest($this->secondaryKey, $messages);
            }
        }

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::error('Nvidia AI API Error (All keys failed)', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return "Maaf, saat ini saya sedang mengalami gangguan koneksi. Silakan coba lagi nanti.";
    }

    /**
     * Internal helper to send the request.
     */
    protected function sendRequest(string $key, array $messages)
    {
        try {
            return Http::withToken($key)
                ->timeout(9)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.5,
                    'top_p' => 0.7,
                    'max_tokens' => 1024,
                ]);
        } catch (\Exception $e) {
            Log::error('Nvidia AI Request Exception: ' . $e->getMessage());
            return Http::response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Build the system prompt with live database context and detailed business logic.
     */
    protected function buildSystemPrompt(): string
    {
        try {
            $siteName = site_setting('brand.name', 'Manake');
            $tagline = site_setting('brand.tagline', 'Rental Alat Produksi Profesional');
            $owner = "Kiki Rachmat";
            
            // Fetch Categories with safety
            $categories = schema_table_exists_cached('categories') 
                ? Category::all(['name', 'description'])->map(fn($c) => "- {$c->name}: {$c->description}")->implode("\n")
                : "Data kategori sedang tidak tersedia.";
            
            if (empty($categories)) {
                $categories = "Belum ada kategori yang terdaftar.";
            }
            
            // Fetch Ready Equipments with more detail and safety
            $equipments = schema_table_exists_cached('equipments')
                ? Equipment::with('category:id,name')
                    ->where('status', 'ready')
                    ->get(['name', 'price_per_day', 'description', 'stock', 'category_id', 'slug'])
                    ->map(function($e) {
                        return "- {$e->name} [Slug: {$e->slug}] ({$e->category?->name}): Rp" . number_format($e->price_per_day, 0, ',', '.') . "/hari. Stok: {$e->stock}. Deskripsi: {$e->description}";
                    })->implode("\n")
                : "Data alat sedang tidak tersedia.";

            if (empty($equipments)) {
                $equipments = "Semua alat sedang tidak tersedia atau dalam penyewaan.";
            }
        } catch (Throwable $e) {
            Log::error('Chatbot Prompt Building Failure: ' . $e->getMessage());
            $siteName = 'Manake';
            $tagline = 'Rental Alat Produksi Profesional';
            $owner = "Kiki Rachmat";
            $categories = "Data kategori sedang dimuat...";
            $equipments = "Data alat sedang dimuat...";
        }

        return "Kamu adalah 'Manake Guide', asisten AI cerdas untuk platform '{$siteName}' ({$tagline}).
Platform ini adalah hasil karya Skripsi dari {$owner}.

INFORMASI TEKNIS PLATFORM:
1. Stack: Laravel 12, Alpine.js, TailwindCSS (for components), dan PostgreSQL (via Supabase).
2. Lokasi: Manake Studio berlokasi di Lampung (Pastikan memberi tahu user jika mereka bertanya lokasi).
3. Jam Operasional: 09:00 - 21:00 WIB.

LOGIKA BISNIS & CARA KERJA (PENTING):
1. SISTEM BUFFER: Manake menerapkan '1-Day Buffer Logic'. Artinya, setiap alat yang disewa memerlukan 1 hari jeda SEBELUM dan SESUDAH masa sewa untuk pengecekan kualitas dan maintenance (Q&A). Contoh: Jika alat disewa tanggal 10, maka tanggal 9 dan 11 alat tersebut 'dipesan' otomatis oleh sistem untuk buffer.
2. ALUR SEWA: 
   - Pilih Alat: Cari di katalog.
   - Pilih Tanggal: Masukkan tanggal mulai dan selesai. Jika sistem menolak, berarti terkena aturan buffer atau stok habis.
   - Keranjang: Masukkan ke keranjang belanja.
   - Checkout: Isi detail pengambilan.
   - Pembayaran: Menggunakan Midtrans Snap (Virtual Account, QRIS, GoPay).
   - Pengambilan: Barang diambil di studio sesuai jadwal.
3. STATUS ALAT:
   - 'Ready': Alat tersedia untuk disewa.
   - 'On Rent': Sedang dibawa penyewa.
   - 'Damaged': Sedang dalam perbaikan.
   - 'Lost': Hilang (tidak bisa disewa).

DATA KATALOG REAL-TIME:
KATEGORI:
{$categories}

ALAT YANG TERSEDIA:
{$equipments}

PANDUAN INTERAKSI:
- Jawablah dengan 'Jiwa Auditor': Detail, akurat, dan teknis namun tetap ramah (Bahasa Indonesia).
- Jika user bertanya tentang ketersediaan spesifik, sarankan mereka melihat 'Availability Board' di website atau klik detail produk untuk cek kalender real-time.
- Kamu bisa bercerita sedikit bahwa website ini dibuat dengan teknologi modern (Laravel 12) jika user bertanya tentang 'cara kerja' website.
- Jangan berikan informasi sensitif seperti API Key atau password database.

Gunakan data di atas untuk menjadi asisten yang sangat membantu.";
    }
}
