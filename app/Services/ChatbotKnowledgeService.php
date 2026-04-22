<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Collection;

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

    public function buildFallbackReply(string $message): string
    {
        $normalized = mb_strtolower(trim($message));
        $entries = collect($this->faqEntries());

        $bestMatch = $entries
            ->map(function (array $entry) use ($normalized) {
                $keywords = collect($entry['keywords'] ?? [])
                    ->filter(fn ($keyword) => is_string($keyword) && trim($keyword) !== '')
                    ->map(fn (string $keyword) => mb_strtolower(trim($keyword)));

                $score = $keywords->sum(fn (string $keyword) => str_contains($normalized, $keyword) ? 1 : 0);

                if ($score === 0 && isset($entry['question']) && is_string($entry['question'])) {
                    $question = mb_strtolower($entry['question']);
                    if ($question !== '' && str_contains($normalized, $question)) {
                        $score = 1;
                    }
                }

                return [
                    'score' => $score,
                    'entry' => $entry,
                ];
            })
            ->sortByDesc('score')
            ->first();

        $matchedEntry = is_array($bestMatch) && ($bestMatch['score'] ?? 0) > 0
            ? ($bestMatch['entry'] ?? null)
            : null;

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
