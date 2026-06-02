<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Str;

class ChatbotKnowledgeService
{
    public function faqEntries(): array
    {
        $entries = config('chatbot.faqs', []);

        return is_array($entries) ? array_values(array_filter($entries, 'is_array')) : [];
    }

    public function faqPreview(int $limit = 5): array
    {
        return array_slice($this->faqEntries(), 0, $limit);
    }

    public function buildInstantReply(string $message): ?string
    {
        $normalized = $this->normalize($message);

        if ($normalized === '') {
            return null;
        }

        $equipmentReply = $this->equipmentReply($normalized);
        if ($equipmentReply !== null) {
            return $equipmentReply;
        }

        $categoryReply = $this->categoryReply($normalized);
        if ($categoryReply !== null) {
            return $categoryReply;
        }

        $matchedEntry = $this->bestFaqMatch($normalized, 2);
        if (is_array($matchedEntry)) {
            return trim((string) ($matchedEntry['answer'] ?? '')) ?: null;
        }

        return null;
    }

    public function buildFallbackReply(string $message): string
    {
        $normalized = $this->normalize($message);
        $entries = collect($this->faqEntries());
        $matchedEntry = $this->bestFaqMatch($normalized, 1);

        $intro = trim((string) config('chatbot.fallback_intro', ''));
        $parts = [];

        if ($intro !== '') {
            $parts[] = $intro;
        }

        if (is_array($matchedEntry)) {
            $parts[] = trim((string) ($matchedEntry['answer'] ?? ''));
        } else {
            $parts[] = 'Saya belum menemukan jawaban yang sangat spesifik dari pertanyaan itu.';
        }

        $catalogSummary = $this->catalogSummary();
        if ($catalogSummary !== '') {
            $parts[] = $catalogSummary;
        }

        $faqList = $entries
            ->take(4)
            ->pluck('question')
            ->filter(fn ($question) => is_string($question) && trim($question) !== '')
            ->map(fn (string $question) => '- ' . trim($question))
            ->implode("\n");

        if ($faqList !== '') {
            $parts[] = "Topik yang bisa langsung saya bantu:\n{$faqList}";
        }

        return implode("\n\n", array_values(array_filter($parts, fn ($part) => is_string($part) && trim($part) !== '')));
    }

    private function equipmentReply(string $normalized): ?string
    {
        if (! schema_table_exists_cached('equipments')) {
            return null;
        }

        $intentKeywords = [
            'harga', 'price', 'biaya', 'sewa', 'stok', 'stock', 'tersedia', 'ready',
            'detail', 'spesifikasi', 'unit', 'booking', 'alat',
        ];

        if (! collect($intentKeywords)->contains(fn (string $keyword) => str_contains($normalized, $keyword))) {
            return null;
        }

        $equipments = Equipment::with('category:id,name')
            ->orderBy('name')
            ->limit(80)
            ->get(['id', 'name', 'slug', 'price_per_day', 'stock', 'status', 'category_id']);

        $match = $equipments
            ->map(function (Equipment $equipment) use ($normalized) {
                $name = $this->normalize($equipment->name);
                $slug = $this->normalize((string) ($equipment->slug ?: Str::slug($equipment->name)));
                $tokens = collect(explode(' ', $name))
                    ->filter(fn (string $token) => mb_strlen($token) >= 3)
                    ->unique()
                    ->values();

                $score = 0;
                if ($name !== '' && str_contains($normalized, $name)) {
                    $score += 5;
                }
                if ($slug !== '' && str_contains(str_replace(' ', '-', $normalized), $slug)) {
                    $score += 5;
                }

                $score += $tokens->sum(fn (string $token) => str_contains($normalized, $token) ? 1 : 0);

                return [
                    'score' => $score,
                    'equipment' => $equipment,
                ];
            })
            ->sortByDesc('score')
            ->first();

        if (! is_array($match) || ($match['score'] ?? 0) < 2) {
            return null;
        }

        /** @var Equipment $equipment */
        $equipment = $match['equipment'];
        $price = number_format((int) $equipment->price_per_day, 0, ',', '.');
        $status = $equipment->status === 'ready' ? 'tersedia' : 'belum tersedia';
        $category = $equipment->category?->name ?: 'Tanpa kategori';
        $slug = $equipment->slug ?: Str::slug($equipment->name);

        return "{$equipment->name} masuk kategori {$category}. Harga sewanya Rp{$price}/hari, stok tercatat {$equipment->stock} unit, dan statusnya {$status}. Untuk booking, buka /product/{$slug}, pilih tanggal sewa, lalu tambahkan ke keranjang.";
    }

    private function categoryReply(string $normalized): ?string
    {
        if (! schema_table_exists_cached('categories') || ! schema_table_exists_cached('equipments')) {
            return null;
        }

        if (! collect(['kategori', 'category', 'alat apa', 'list alat', 'daftar alat', 'rekomendasi'])->contains(fn (string $keyword) => str_contains($normalized, $keyword))) {
            return null;
        }

        $categories = Category::query()
            ->withCount(['equipments as ready_equipments_count' => fn ($query) => $query->where('status', 'ready')])
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name']);

        if ($categories->isEmpty()) {
            return null;
        }

        $summary = $categories
            ->map(fn (Category $category) => "{$category->name} ({$category->ready_equipments_count} ready)")
            ->implode(', ');

        return "Kategori alat yang tersedia di Manake saat ini: {$summary}. Untuk melihat item lengkap dan memilih tanggal, buka halaman katalog atau cek ketersediaan alat.";
    }

    private function bestFaqMatch(string $normalized, int $minimumScore): ?array
    {
        $bestMatch = collect($this->faqEntries())
            ->map(function (array $entry) use ($normalized) {
                $keywords = collect($entry['keywords'] ?? [])
                    ->filter(fn ($keyword) => is_string($keyword) && trim($keyword) !== '')
                    ->map(fn (string $keyword) => $this->normalize($keyword));

                $score = $keywords->sum(fn (string $keyword) => $keyword !== '' && str_contains($normalized, $keyword) ? 2 : 0);

                if (isset($entry['question']) && is_string($entry['question'])) {
                    $question = $this->normalize($entry['question']);
                    if ($question !== '' && (str_contains($normalized, $question) || str_contains($question, $normalized))) {
                        $score += 3;
                    }
                }

                return [
                    'score' => $score,
                    'entry' => $entry,
                ];
            })
            ->sortByDesc('score')
            ->first();

        return is_array($bestMatch) && ($bestMatch['score'] ?? 0) >= $minimumScore
            ? ($bestMatch['entry'] ?? null)
            : null;
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/', ' ', mb_strtolower(trim($value))) ?: '';
    }

    private function catalogSummary(): string
    {
        if (! schema_table_exists_cached('equipments')) {
            return '';
        }

        $equipmentQuery = Equipment::query();

        $total = (clone $equipmentQuery)->count();
        $readyCount = (clone $equipmentQuery)->where('status', 'ready')->count();

        $topCategories = schema_table_exists_cached('categories')
            ? Category::query()
                ->select('name')
                ->orderBy('name')
                ->limit(4)
                ->pluck('name')
                ->filter(fn ($name) => is_string($name) && trim($name) !== '')
                ->implode(', ')
            : '';

        if ($total === 0) {
            return 'Saat ini saya belum melihat data katalog alat aktif di sistem.';
        }

        $parts = [
            "Katalog saat ini memuat {$total} alat, dengan {$readyCount} alat berstatus siap.",
        ];

        if ($topCategories !== '') {
            $parts[] = "Kategori yang terdeteksi: {$topCategories}.";
        }

        return implode(' ', $parts);
    }
}
