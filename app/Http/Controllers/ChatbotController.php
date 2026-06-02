<?php

namespace App\Http\Controllers;

use App\Services\ChatbotKnowledgeService;
use App\Services\Ai\GeminiAiService;
use App\Services\Ai\LocalAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected LocalAiService $aiService;
    protected GeminiAiService $geminiService;
    protected ChatbotKnowledgeService $knowledgeService;

    public function __construct(LocalAiService $aiService, GeminiAiService $geminiService, ChatbotKnowledgeService $knowledgeService)
    {
        $this->aiService = $aiService;
        $this->geminiService = $geminiService;
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the chat message from the frontend.
     */
    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:500',
            ]);

            if ($this->isOutOfManakeScope($request->message) && ! $this->knowledgeService->isCatalogRelated($request->message)) {
                return response()->json([
                    'message' => 'Maaf, saya hanya bisa membantu pertanyaan seputar Manake: katalog alat, cara sewa, ketersediaan, pembayaran, order, lokasi, dan aturan rental.',
                ]);
            }

            $instantReply = $this->knowledgeService->buildInstantReply($request->message);
            if ($instantReply !== null) {
                return response()->json([
                    'message' => $instantReply,
                ]);
            }

            // Get or initialize chat history from session (limited to last 10 exchanges for token safety)
            $history = Session::get('chatbot_history', []);
            
            // Add user message to history
            $history[] = [
                'role' => 'user',
                'content' => $request->message,
            ];

            // Prefer the Singapore/Ollama host, then Gemini, then the curated Manake FAQ.
            $aiResponse = trim((string) $this->aiService->chat($history));

            if ($this->shouldFallbackToKnowledgeBase($aiResponse)) {
                $aiResponse = trim((string) $this->geminiService->chat($history));
            }

            if ($this->shouldFallbackToKnowledgeBase($aiResponse)) {
                $aiResponse = $this->knowledgeService->buildFallbackReply($request->message);
            }

            // Add AI response to history
            $history[] = [
                'role' => 'assistant',
                'content' => $aiResponse,
            ];

            // Trim history if too long (keep last 10 messages)
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }

            Session::put('chatbot_history', $history);

            return response()->json([
                'message' => $aiResponse,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Chatbot Controller Error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => "Maaf, Manake Guide sedang bermasalah. Coba lagi sebentar.",
            ], 500);
        }
    }

    /**
     * Reset the chat history.
     */
    public function reset()
    {
        Session::forget('chatbot_history');
        return response()->json(['status' => 'success']);
    }

    private function shouldFallbackToKnowledgeBase(string $response): bool
    {
        if ($response === '') {
            return true;
        }

        $normalized = mb_strtolower($response);

        foreach ([
            'api key belum dikonfigurasi',
            'sedang dalam pemeliharaan',
            'gangguan koneksi ke mesin ai',
            'kesalahan internal pada sistem asisten digital',
            'sistem ai sedang mengalami gangguan teknis',
            'mesin ai lokal kami sedang sibuk',
            'kesalahan teknis pada sistem ai lokal',
            'gemini api key belum dikonfigurasi',
            'fallback gemini sedang tidak tersedia',
            'fallback gemini sedang mengalami gangguan teknis',
            'gemini tidak mengembalikan jawaban yang valid',
        ] as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function isOutOfManakeScope(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));

        if ($normalized === '') {
            return false;
        }

        foreach (['halo', 'hai', 'hi', 'hello', 'pagi', 'siang', 'malam', 'terima kasih', 'makasih', 'thanks'] as $greeting) {
            if ($normalized === $greeting || str_starts_with($normalized, $greeting.' ')) {
                return false;
            }
        }

        foreach ([
            'manake', 'sewa', 'rental', 'alat', 'equipment', 'kamera', 'camera', 'lighting', 'lampu',
            'audio', 'mic', 'microphone', 'ht', 'handy talky', 'drone', 'stabilizer', 'gimbal',
            'katalog', 'catalog', 'produk', 'product', 'stok', 'stock', 'tersedia', 'ketersediaan',
            'availability', 'booking', 'pemesanan', 'jadwal', 'tanggal', 'harga', 'price', 'biaya',
            'cart', 'keranjang', 'checkout', 'payment', 'pembayaran', 'midtrans', 'qris', 'order',
            'pesanan', 'riwayat', 'invoice', 'receipt', 'profil', 'profile', 'login', 'akun',
            'register', 'lokasi', 'alamat', 'maps', 'jam', 'pickup', 'ambil', 'pengambilan',
            'return', 'pengembalian', 'buffer', 'reschedule', 'refund', 'denda', 'rusak', 'damage',
            'support', 'kontak', 'admin', 'website',
            'berapa', 'harganya', 'harga sewa', 'berapa sewa', 'unit', 'ready', 'kosong', 'penuh',
            'kapan', 'dimana', 'balikin', 'telat', 'ktp', 'identitas', 'syarat', 'dp', 'deposit',
            'transfer', 'va', 'virtual account', 'e-wallet', 'faktur',
        ] as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return false;
            }
        }

        return true;
    }
}
