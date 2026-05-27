<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiAiService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $model;

    protected int $timeoutSeconds;

    protected ManakeChatPrompt $prompt;

    public function __construct(?ManakeChatPrompt $prompt = null)
    {
        $this->apiKey = trim((string) config('services.gemini.api_key', ''));
        $this->baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $this->timeoutSeconds = max(2, (int) config('services.gemini.timeout', 12));
        $this->prompt = $prompt ?: app(ManakeChatPrompt::class);
    }

    public function chat(array $messages): string
    {
        if ($this->apiKey === '') {
            return 'Maaf, Gemini API key belum dikonfigurasi.';
        }

        try {
            $contents = $this->toGeminiContents($messages);

            $response = Http::timeout($this->timeoutSeconds)
                ->connectTimeout(min($this->timeoutSeconds, 5))
                ->withHeaders([
                    'x-goog-api-key' => $this->apiKey,
                ])
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent", [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.35,
                        'topP' => 0.7,
                        'maxOutputTokens' => 360,
                    ],
                ]);

            if ($response->successful()) {
                $content = trim((string) $response->json('candidates.0.content.parts.0.text', ''));

                return $content !== '' ? $content : 'Maaf, Gemini tidak mengembalikan jawaban yang valid.';
            }

            Log::warning('Gemini chatbot fallback failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Maaf, fallback Gemini sedang tidak tersedia.';
        } catch (Throwable $e) {
            Log::warning('GeminiAiService::chat failure: '.$e->getMessage(), ['exception' => $e]);

            return 'Maaf, fallback Gemini sedang mengalami gangguan teknis.';
        }
    }

    private function toGeminiContents(array $messages): array
    {
        $contents = [];
        $systemPrompt = $this->prompt->build();
        $systemApplied = false;

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = trim((string) ($message['content'] ?? ''));

            if ($content === '' || $role === 'system') {
                continue;
            }

            if (! $systemApplied && $role === 'user') {
                $content = $systemPrompt."\n\nPERTANYAAN USER:\n".$content;
                $systemApplied = true;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        if ($contents === []) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $systemPrompt."\n\nPERTANYAAN USER:\nHalo"],
                ],
            ];
        }

        return $contents;
    }
}
