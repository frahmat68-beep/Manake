<?php

namespace Tests\Feature;

use App\Services\Ai\NvidiaAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_returns_faq_fallback_when_ai_service_is_unavailable(): void
    {
        $mock = $this->mock(NvidiaAiService::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('Maaf, sistem AI sedang dalam pemeliharaan (API Key belum dikonfigurasi). Silakan coba lagi nanti.');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'bagaimana cara sewa alat di manake?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Pilih alat di katalog'));
    }
}
