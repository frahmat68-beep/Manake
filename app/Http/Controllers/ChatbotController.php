<?php

namespace App\Http\Controllers;

use App\Services\Ai\NvidiaAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected NvidiaAiService $aiService;

    public function __construct(NvidiaAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle the chat message from the frontend.
     */
    public function chat(Request $request)
    {
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
        $aiResponse = $this->aiService->chat($history);

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
    }

    /**
     * Reset the chat history.
     */
    public function reset()
    {
        Session::forget('chatbot_history');
        return response()->json(['status' => 'success']);
    }
}
