<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Models\Equipment;
use App\Services\AvailabilityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Build the system prompt with live database context.
     */
    protected function buildSystemPrompt(): string
    {
        $siteName = site_setting('brand.name', 'Manake');
        $tagline = site_setting('brand.tagline', 'Rental Alat Produksi Profesional');
        
        $categories = Category::all(['name', 'description'])->map(fn($c) => "- {$c->name}: {$c->description}")->implode("\n");
        
        $equipments = Equipment::with('category:id,name')
            ->where('status', 'ready')
            ->get(['name', 'price_per_day', 'description', 'stock', 'category_id'])
            ->map(function($e) {
                return "- {$e->name} ({$e->category?->name}): Rp" . number_format($e->price_per_day, 0, ',', '.') . "/hari. Stok: {$e->stock}. Deskripsi: {$e->description}";
            })->implode("\n");

        return "Kamu adalah 'Manake Guide', asisten AI pintar untuk website '{$siteName}' ({$tagline}).
Tugasmu adalah membantu pelanggan (baik tamu maupun member) menjawab pertanyaan seputar katalog alat, harga, ketersediaan, dan cara kerja sewa.

ATURAN UTAMA:
1. Jawablah dengan sopan, ramah, dan profesional dalam Bahasa Indonesia.
2. Manake Rental menggunakan sistem '1-day buffer'. Artinya, setiap sewa butuh 1 hari jeda sebelum dan sesudah untuk pengecekan alat.
3. Alur sewa: Pilih alat -> Masukkan Keranjang -> Checkout -> Bayar via Midtrans (Snap) -> Ambil Barang.
4. Kamu HANYA tahu data yang ada di katalog di bawah ini. Jika ditanya alat yang tidak ada, jawab dengan jujur bahwa alat tersebut belum tersedia.
5. Jangan pernah membocorkan data pribadi user lain atau sistem internal di luar yang diberikan.
6. Website Manake didukung oleh Laravel 12 dan Supabase (PostgreSQL).

DATA KATALOG & KATEGORI SAAT INI:
KATEGORI:
{$categories}

ALAT & HARGA:
{$equipments}

Gunakan data di atas untuk menjawab pertanyaan user secara akurat.";
    }
}
