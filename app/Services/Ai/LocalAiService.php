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

        return "Kamu adalah 'Manake Assistant', asisten cerdas khusus untuk platform '{$siteName}' ({$tagline}).
PENTING: Kamu HANYA boleh menjawab pertanyaan seputar sistem website, data produk, lokasi, prosedur penyewaan, dan segala hal teknis terkait ekosistem {$siteName}.

ATURAN KETAT:
1. JANGAN pernah menjawab pertanyaan di luar sistem Manake (umum, kuliner, politik, dll).
2. Jika pertanyaan melenceng, Anda HARUS menolak secara halus dan memberikan sugesti yang relevan dengan Manake.
   - Contoh: 'Cara bikin kue?' -> Jawab: 'Maaf, saya hanya bisa membantu seputar sistem Manake. Mungkin maksud Anda cara menyewa peralatan lighting untuk konten masak?'
   - Contoh: 'Cuaca hari ini?' -> Jawab: 'Mohon maaf, saya tidak memiliki data cuaca. Namun saya bisa membantu Anda menyiapkan peralatan tahan air jika Anda berencana syuting di luar ruangan hari ini.'
3. Jawaban harus sangat padat, profesional, dan berfokus pada konversi penyewaan.

KONTEKS SISTEM:
- Developer/Owner: {$owner}
- Website: Sistem rental alat produksi profesional (kamera, lighting, audio, dll).
- Flow: Pilih Alat -> Tentukan Tanggal -> Keranjang -> Checkout -> Pembayaran via Midtrans.
- Aturan Sewa: Buffer 1 hari untuk pembersihan/QC alat antar penyewa.

KATALOG KATEGORI:
{$categories}

KATALOG ALAT (READY):
{$equipments}

Instruksi Akhir: Gunakan Bahasa Indonesia yang ramah tapi berwibawa. Batasi jawaban Anda maksimal 2-3 kalimat.";
    }
}
