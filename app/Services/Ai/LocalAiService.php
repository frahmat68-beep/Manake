<?php

namespace App\Services\Ai;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LocalAiService
{
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.ollama.base_url', 'http://152.69.218.198:11434');
        $this->model = (string) config('services.ollama.model', 'qwen2:0.5b');
    }

    /**
     * Send a message to the local AI (Ollama) and get a response.
     */
    public function chat(array $messages)
    {
        try {
            if (!collect($messages)->contains('role', 'system')) {
                array_unshift($messages, [
                    'role' => 'system',
                    'content' => $this->buildSystemPrompt(),
                ]);
            }

            $response = Http::timeout(60) // High timeout for swap-based AI
                ->post("{$this->baseUrl}/api/chat", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.5,
                        'top_p' => 0.7,
                        'num_predict' => 512,
                    ],
                ]);

            if ($response->successful()) {
                $content = $response->json('message.content');
                return $content ?: "Maaf, saya tidak mendapatkan respon yang valid dari AI Lokal.";
            }

            Log::error('Local AI (Ollama) API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return "Maaf, mesin AI lokal kami sedang sibuk atau mengalami kendala. Silakan coba beberapa saat lagi.";
        } catch (Throwable $e) {
            Log::critical('LocalAiService::chat failure: ' . $e->getMessage(), ['exception' => $e]);
            return "Maaf, terjadi kesalahan teknis pada sistem AI lokal.";
        }
    }

    /**
     * Build the system prompt with live database context.
     */
    protected function buildSystemPrompt(): string
    {
        try {
            $siteName = site_setting('brand.name', 'Manake');
            $tagline = site_setting('brand.tagline', 'Rental Alat Produksi Profesional');
            $owner = "Kiki Rachmat";
            
            $categories = schema_table_exists_cached('categories') 
                ? Category::all(['name'])->map(fn($c) => "- {$c->name}")->implode("\n")
                : "Data kategori sedang tidak tersedia.";
            
            $equipments = schema_table_exists_cached('equipments')
                ? Equipment::with('category:id,name')
                    ->where('status', 'ready')
                    ->limit(20) // Limit to save context space for small models
                    ->get(['name', 'price_per_day', 'stock', 'category_id'])
                    ->map(function($e) {
                        return "- {$e->name} ({$e->category?->name}): Rp" . number_format($e->price_per_day, 0, ',', '.') . "/hari. Stok: {$e->stock}";
                    })->implode("\n")
                : "Data alat sedang tidak tersedia.";

        } catch (Throwable $e) {
            Log::error('Local Chatbot Prompt Building Failure: ' . $e->getMessage());
            $siteName = 'Manake';
            $tagline = 'Rental Alat Produksi Profesional';
            $owner = "Kiki Rachmat";
            $categories = "Data kategori...";
            $equipments = "Data alat...";
        }

        return "Kamu adalah 'Manake Guide', asisten AI khusus untuk platform '{$siteName}' ({$tagline}).
PENTING: Kamu HANYA boleh menjawab pertanyaan seputar sistem website, data produk, lokasi, dan prosedur penyewaan di Manake.

BATASAN TOPIK:
1. Jika user bertanya hal yang TIDAK berhubungan dengan Manake (contoh: cara memasak, politik, umum), JANGAN dijawab. 
2. Alihkan pertanyaan tersebut ke fitur Manake. Contoh: 'Cara bikin kue?' dijawab dengan 'Maaf, saya hanya asisten untuk Manake. Mungkin maksud Anda cara memesan alat untuk dokumentasi masak?'
3. Fokus utama: Membantu user menyewa alat.

LOGIKA BISNIS:
1. 1-Day Buffer: Ada jeda 1 hari sebelum & sesudah sewa untuk QC.
2. Alur: Pilih Alat -> Pilih Tanggal -> Keranjang -> Checkout -> Midtrans.
3. Status: 'Ready', 'On Rent', 'Damaged', 'Lost'.

KATALOG:
KATEGORI:
{$categories}

ALAT READY:
{$equipments}

Instruksi: Jawab sangat singkat, ramah, dan selalu arahkan ke transaksi penyewaan (Bahasa Indonesia).";
    }
}
