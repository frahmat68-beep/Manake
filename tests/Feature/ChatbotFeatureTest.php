<?php

namespace Tests\Feature;

use App\Services\Ai\LocalAiService;
use App\Services\Ai\GeminiAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_returns_faq_fallback_when_ai_service_is_unavailable(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
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

    public function test_chatbot_uses_gemini_when_singapore_ai_is_unavailable(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('Maaf, mesin AI lokal kami sedang sibuk atau mengalami kendala. Silakan coba beberapa saat lagi.');
        });

        $this->mock(GeminiAiService::class, function ($mock): void {
            $mock->shouldReceive('chat')
                ->once()
                ->andReturn('Untuk cek ketersediaan alat Manake, buka Availability Board atau detail produk lalu pilih tanggal sewa.');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'cara cek ketersediaan alat manake?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Untuk cek ketersediaan alat Manake, buka Availability Board atau detail produk lalu pilih tanggal sewa.');
    }

    public function test_chatbot_rejects_questions_outside_manake_scope(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $this->mock(GeminiAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'siapa presiden amerika sekarang?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'hanya bisa membantu pertanyaan seputar Manake'));
    }
}
