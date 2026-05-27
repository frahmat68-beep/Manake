<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LocalAiService
{
    protected string $baseUrl;

    protected string $model;

    protected int $timeoutSeconds;

    protected ManakeChatPrompt $prompt;

    public function __construct(?ManakeChatPrompt $prompt = null)
    {
        $this->baseUrl = rtrim((string) config('services.ollama.base_url', 'http://152.69.218.198:11434'), '/');
        $this->model = (string) config('services.ollama.model', 'qwen2:0.5b');
        $this->timeoutSeconds = max(2, (int) config('services.ollama.timeout', 8));
        $this->prompt = $prompt ?: app(ManakeChatPrompt::class);
    }

    /**
     * Send a message to the local AI (Ollama) and get a response.
     */
    public function chat(array $messages)
    {
        try {
            if (! collect($messages)->contains('role', 'system')) {
                array_unshift($messages, [
                    'role' => 'system',
                    'content' => $this->buildSystemPrompt(),
                ]);
            }

            $response = Http::timeout($this->timeoutSeconds)
                ->connectTimeout(min($this->timeoutSeconds, 5))
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

                return $content ?: 'Maaf, saya tidak mendapatkan respon yang valid dari AI Lokal.';
            }

            Log::error('Local AI (Ollama) API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Maaf, mesin AI lokal kami sedang sibuk atau mengalami kendala. Silakan coba beberapa saat lagi.';
        } catch (Throwable $e) {
            Log::critical('LocalAiService::chat failure: '.$e->getMessage(), ['exception' => $e]);

            return 'Maaf, terjadi kesalahan teknis pada sistem AI lokal.';
        }
    }

    /**
     * Build the system prompt with live database context.
     */
    protected function buildSystemPrompt(): string
    {
        return $this->prompt->build();
    }
}
