<?php

namespace Tests\Feature;

use App\Services\Ai\LocalAiService;
use App\Services\Ai\GeminiAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_answers_common_faq_instantly_without_ai(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $this->mock(GeminiAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'bagaimana cara sewa alat di manake?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Pilih alat di katalog'));
    }

    public function test_chatbot_uses_gemini_when_singapore_ai_is_unavailable_for_non_instant_queries(): void
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
            'message' => 'Manake guide bisa bantu kebutuhan produksi dokumentasi acara?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Untuk cek ketersediaan alat Manake, buka Availability Board atau detail produk lalu pilih tanggal sewa.');
    }

    public function test_chatbot_answers_equipment_price_from_catalog_data(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $this->mock(GeminiAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $category = \App\Models\Category::create([
            'name' => 'Aksesoris',
            'slug' => 'aksesoris',
        ]);

        \App\Models\Equipment::create([
            'category_id' => $category->id,
            'name' => 'V-Mount Charger 2 Slot',
            'slug' => 'vmount-charger',
            'price_per_day' => 50000,
            'stock' => 2,
            'status' => 'ready',
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'berapa harga sewa vmount charger?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Rp50.000/hari')
                && str_contains($message, 'stok tercatat 2 unit'));
    }

    public function test_chatbot_recognizes_partial_equipment_name_as_manake_scope(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $this->mock(GeminiAiService::class, function ($mock): void {
            $mock->shouldNotReceive('chat');
        });

        $category = \App\Models\Category::create([
            'name' => 'Audio',
            'slug' => 'audio',
        ]);

        \App\Models\Equipment::create([
            'category_id' => $category->id,
            'name' => 'Saramonic Blink 500 T4 Wireless',
            'slug' => 'saramonic-blink500',
            'price_per_day' => 100000,
            'stock' => 1,
            'status' => 'ready',
        ]);

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'saya mau sewa saramonic di tanggal 4 bisa?',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'Saramonic Blink 500 T4 Wireless')
                && str_contains($message, 'pilih tanggal mulai dan selesai'));
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

    public function test_chatbot_rejects_empty_message(): void
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_chatbot_rejects_too_long_message(): void
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => str_repeat('a', 501),
        ]);

        $response->assertStatus(422);
    }

    public function test_chatbot_accepts_common_short_rental_queries(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldReceive('chat')->andReturn('Harga sewa tertera di katalog.');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'berapa sewanya?',
        ]);

        $response->assertOk()->assertJsonStructure(['message']);
    }

    public function test_chatbot_accepts_catalog_login_question(): void
    {
        $this->mock(LocalAiService::class, function ($mock): void {
            $mock->shouldReceive('chat')->andReturn('Cek katalog bebas tanpa login.');
        });

        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'bisa cek tanpa login?',
        ]);

        $response->assertOk()->assertJsonStructure(['message']);
    }

    public function test_chatbot_rejects_unrelated_recipe_questions(): void
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'buatkan resep masakan nasi goreng',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', fn (string $message) => str_contains($message, 'hanya bisa membantu pertanyaan seputar Manake'));
    }

    public function test_chatbot_reset_clears_session_history(): void
    {
        $response = $this->postJson(route('chatbot.reset'));
        $response->assertOk()->assertJson(['status' => 'success']);
    }
}
