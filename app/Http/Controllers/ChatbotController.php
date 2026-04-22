<?php

namespace App\Http\Controllers;

use App\Services\ChatbotKnowledgeService;
use App\Services\Ai\NvidiaAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected NvidiaAiService $aiService;
    protected ChatbotKnowledgeService $knowledgeService;

    public function __construct(NvidiaAiService $aiService, ChatbotKnowledgeService $knowledgeService)
    {
        $this->aiService = $aiService;
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

            // Get or initialize chat history from session (limited to last 10 exchanges for token safety)
            $history = Session::get('chatbot_history', []);
            
            // Add user message to history
            $history[] = [
                'role' => 'user',
                'content' => $request->message,
            ];

            // Call the AI service
            $aiResponse = trim((string) $this->aiService->chat($history));
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Chatbot Controller Error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => "Maaf, sistem AI sedang mengalami gangguan teknis. Silakan coba lagi nanti.",
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
        ] as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }
}
