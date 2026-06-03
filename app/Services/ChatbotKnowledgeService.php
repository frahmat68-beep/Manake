<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        // 1. Privacy filter check
        if (collect(['siapa yang sewa', 'siapa penyewa', 'siapa yang booking', 'data penyewa', 'identitas penyewa', 'nama yang sewa', 'nomor tlp yang sewa', 'alamat yang sewa', 'nik yang sewa'])->contains(fn ($kw) => str_contains($normalized, $kw))) {
            return 'Demi alasan keamanan dan privasi pengguna Manake, data pribadi penyewa serta nomor kontak penyewa tidak dapat dibagikan di sini. Informasi jadwal sewa alat secara umum dapat dipantau langsung melalui halaman Availability Board.';
        }

        // 2. Current rented items check
        if (collect(['alat yang sedang disewa', 'yang disewa hari ini', 'sedang disewa', 'sedang terpakai', 'alat yang sedang dipakai', 'sedang dipinjam'])->contains(fn ($kw) => str_contains($normalized, $kw))) {
            return $this->currentRentedReply();
        }

        // 3. General availability check
        if (collect(['alat yang tersedia hari ini', 'ready hari ini', 'kosong hari ini', 'ketersediaan hari ini', 'alat apa saja yang ready', 'alat apa yang tersedia', 'stok yang ready hari ini'])->contains(fn ($kw) => str_contains($normalized, $kw))) {
            return $this->generalAvailabilityReply();
        }

        // 4. Equipment reply (matches specific item)
        $equipmentReply = $this->equipmentReply($normalized);
        if ($equipmentReply !== null) {
            return $equipmentReply;
        }

        // 5. Category availability check
        $categoryReply = $this->categoryReply($normalized);
        if ($categoryReply !== null) {
            return $categoryReply;
        }

        // 6. FAQ match
        $matchedEntry = $this->bestFaqMatch($normalized, 2);
        if (is_array($matchedEntry)) {
            return trim((string) ($matchedEntry['answer'] ?? '')) ?: null;
        }

        return null;
    }

    public function isCatalogRelated(string $message): bool
    {
        $normalized = $this->normalize($message);

        if ($normalized === '' || ! schema_table_exists_cached('equipments')) {
            return false;
        }

        $equipmentMatch = $this->findEquipmentMatch($normalized);
        if (is_array($equipmentMatch) && ($equipmentMatch['score'] ?? 0) >= 1) {
            return true;
        }

        if (schema_table_exists_cached('categories')) {
            return Category::query()
                ->pluck('name')
                ->filter(fn ($name) => is_string($name) && trim($name) !== '')
                ->contains(fn (string $name) => str_contains($normalized, $this->normalize($name)));
        }

        return false;
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

        $hasRentalIntent = collect($intentKeywords)->contains(fn (string $keyword) => str_contains($normalized, $keyword));
        $match = $this->findEquipmentMatch($normalized);

        if (! $hasRentalIntent && (! is_array($match) || ($match['score'] ?? 0) < 1)) {
            return null;
        }

        if (! is_array($match) || ($match['score'] ?? 0) < 1) {
            return null;
        }

        /** @var Equipment $equipment */
        $equipment = $match['equipment'];
        $price = number_format((int) $equipment->price_per_day, 0, ',', '.');
        $status = $equipment->status === 'ready' ? 'tersedia' : 'belum tersedia';
        $category = $equipment->category?->name ?: 'Tanpa kategori';
        $slug = $equipment->slug ?: Str::slug($equipment->name);

        $parsedDate = $this->parseDateFromText($normalized);
        if ($parsedDate) {
            $dateStr = $parsedDate->locale('id')->isoFormat('D MMMM YYYY');
            if ($equipment->status !== 'ready') {
                return "{$equipment->name} saat ini berstatus tidak aktif/maintenance dan tidak dapat disewa pada tanggal {$dateStr}.";
            }
            $availability = app(\App\Services\AvailabilityService::class);
            $eval = $availability->evaluateRange($equipment, $parsedDate, $parsedDate, 1);
            if ($eval['ok']) {
                $daily = $eval['daily'][$parsedDate->toDateString()] ?? [];
                $reserved = (int) ($daily['qty'] ?? 0);
                $remaining = max((int) $equipment->stock - $reserved, 0);
                return "{$equipment->name} TERSEDIA untuk disewa pada tanggal {$dateStr}. Sisa stok yang ready: {$remaining} dari total {$equipment->stock} unit. Harga sewa Rp{$price}/hari. Silakan buka /product/{$slug} untuk memesan.";
            } else {
                return "Maaf, {$equipment->name} TIDAK TERSEDIA (sudah penuh dibooking / masuk masa buffer sewa) pada tanggal {$dateStr}. Silakan cek alternatif tanggal lain di /product/{$slug} atau lihat Availability Board.";
            }
        }

        $dateHint = str_contains($normalized, 'tanggal') || preg_match('/\b\d{1,2}\b/', $normalized)
            ? ' Saya belum bisa memastikan tanggal hanya dari angka itu; pilih tanggal mulai dan selesai di halaman produk supaya sistem mengecek stok real-time.'
            : '';

        return "{$equipment->name} masuk kategori {$category}. Harga sewanya Rp{$price}/hari, stok tercatat {$equipment->stock} unit, dan statusnya {$status}.{$dateHint} Untuk booking, buka /product/{$slug}, pilih tanggal sewa, lalu tambahkan ke keranjang.";
    }

    private function findEquipmentMatch(string $normalized): ?array
    {
        if (! schema_table_exists_cached('equipments')) {
            return null;
        }

        return Equipment::with('category:id,name')
            ->orderBy('name')
            ->limit(120)
            ->get(['id', 'name', 'slug', 'price_per_day', 'stock', 'status', 'category_id'])
            ->map(function (Equipment $equipment) use ($normalized) {
                $name = $this->normalize($equipment->name);
                $slug = $this->normalize((string) ($equipment->slug ?: Str::slug($equipment->name)));
                $hyphenatedMessage = str_replace(' ', '-', $normalized);
                $tokens = collect(explode(' ', str_replace(['-', '/', '_'], ' ', $name.' '.$slug)))
                    ->filter(fn (string $token) => mb_strlen($token) >= 4)
                    ->unique()
                    ->values();

                $score = 0;
                if ($name !== '' && str_contains($normalized, $name)) {
                    $score += 8;
                }
                if ($slug !== '' && str_contains($hyphenatedMessage, $slug)) {
                    $score += 8;
                }

                $score += $tokens->sum(fn (string $token) => str_contains($normalized, $token) ? 2 : 0);

                return [
                    'score' => $score,
                    'equipment' => $equipment,
                ];
            })
            ->sortByDesc('score')
            ->first();
    }

    private function categoryReply(string $normalized): ?string
    {
        if (! schema_table_exists_cached('categories') || ! schema_table_exists_cached('equipments')) {
            return null;
        }

        $categories = Category::query()->get();
        $matchedCategory = null;
        foreach ($categories as $cat) {
            $catNameNorm = $this->normalize($cat->name);
            if (str_contains($normalized, $catNameNorm)) {
                $matchedCategory = $cat;
                break;
            }
        }

        if (!$matchedCategory) {
            if (! collect(['kategori', 'category', 'alat apa', 'list alat', 'daftar alat', 'rekomendasi'])->contains(fn (string $keyword) => str_contains($normalized, $keyword))) {
                return null;
            }

            $categoriesWithCount = Category::query()
                ->withCount(['equipments as ready_equipments_count' => fn ($query) => $query->where('status', 'ready')])
                ->orderBy('name')
                ->limit(8)
                ->get(['id', 'name']);

            if ($categoriesWithCount->isEmpty()) {
                return null;
            }

            $summary = $categoriesWithCount
                ->map(fn (Category $category) => "{$category->name} ({$category->ready_equipments_count} ready)")
                ->implode(', ');

            return "Kategori alat yang tersedia di Manake saat ini: {$summary}. Untuk melihat item lengkap dan memilih tanggal, buka halaman katalog atau cek ketersediaan alat.";
        }

        $equipments = $matchedCategory->equipments()->where('status', 'ready')->get();
        if ($equipments->isEmpty()) {
            return "Kategori {$matchedCategory->name} saat ini tidak memiliki alat yang siap disewa.";
        }

        $today = now()->startOfDay();
        $todayStr = $today->toDateString();
        $availability = app(\App\Services\AvailabilityService::class);
        $allReservations = $availability->getBatchDailyReservedUnits($equipments, $today, $today);

        $availableItems = [];
        foreach ($equipments as $eq) {
            $reserved = (int) data_get($allReservations, $eq->id . '.' . $todayStr . '.qty', 0);
            $available = max((int) $eq->stock - $reserved, 0);
            if ($available > 0) {
                $availableItems[] = "- {$eq->name} (Ready: {$available} unit, Rp" . number_format((int) $eq->price_per_day, 0, ',', '.') . "/hari)";
            }
        }

        if (empty($availableItems)) {
            return "Semua alat dalam kategori {$matchedCategory->name} sedang penuh disewa hari ini. Silakan cek ketersediaan tanggal lain di Availability Board.";
        }

        $top5 = array_slice($availableItems, 0, 5);
        $listStr = implode("\n", $top5);
        return "Berikut adalah alat dalam kategori {$matchedCategory->name} yang tersedia hari ini:\n\n{$listStr}\n\nBuka halaman katalog untuk memesan alat tersebut.";
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

    private function currentRentedReply(): string
    {
        if (! schema_table_exists_cached('equipments') || ! schema_table_exists_cached('order_items')) {
            return 'Belum ada data persewaan alat saat ini.';
        }

        $today = now()->startOfDay();
        $todayStr = $today->toDateString();
        $allReady = Equipment::query()->where('status', 'ready')->get();
        $availability = app(\App\Services\AvailabilityService::class);
        $allReservations = $availability->getBatchDailyReservedUnits($allReady, $today, $today);

        $rentedList = [];
        foreach ($allReady as $eq) {
            $reserved = (int) data_get($allReservations, $eq->id . '.' . $todayStr . '.qty', 0);
            if ($reserved > 0) {
                $rentedList[] = "- {$eq->name} (Disewa sebanyak {$reserved} unit hari ini)";
            }
        }

        if (empty($rentedList)) {
            return 'Hari ini tidak ada alat yang sedang disewa. Semua alat berstatus ready/kosong!';
        }

        $listStr = implode("\n", $rentedList);
        return "Berikut adalah daftar alat yang sedang aktif disewa hari ini (" . now()->locale('id')->isoFormat('D MMMM YYYY') . "):\n\n{$listStr}\n\nUntuk detail jadwal lengkap per alat, kunjungi halaman Availability Board.";
    }

    private function generalAvailabilityReply(): string
    {
        if (! schema_table_exists_cached('equipments')) {
            return 'Belum ada data katalog alat di sistem.';
        }

        $today = now()->startOfDay();
        $todayStr = $today->toDateString();
        $allReady = Equipment::query()->where('status', 'ready')->get();
        $totalReadyCount = $allReady->count();

        $availability = app(\App\Services\AvailabilityService::class);
        $allReservations = $availability->getBatchDailyReservedUnits($allReady, $today, $today);

        $availableList = [];
        $rentedCount = 0;
        foreach ($allReady as $eq) {
            $reserved = (int) data_get($allReservations, $eq->id . '.' . $todayStr . '.qty', 0);
            $available = max((int) $eq->stock - $reserved, 0);
            if ($reserved > 0) {
                $rentedCount++;
            }
            if ($available > 0) {
                $availableList[] = "- {$eq->name} (Tersisa {$available} unit)";
            }
        }

        $freeCount = $totalReadyCount - $rentedCount;
        $topAvailable = array_slice($availableList, 0, 5);
        $topAvailableStr = implode("\n", $topAvailable);

        return "Informasi ketersediaan alat hari ini (" . now()->locale('id')->isoFormat('D MMMM YYYY') . "):\n" .
            "- Total alat aktif: {$totalReadyCount} tipe alat\n" .
            "- Alat terpakai/disewa: {$rentedCount} tipe\n" .
            "- Alat kosong/tersedia: {$freeCount} tipe\n\n" .
            "Beberapa alat yang tersedia untuk dipesan hari ini:\n{$topAvailableStr}\n\n" .
            "Cek jadwal ketersediaan lengkap di halaman /availability-board.";
    }

    private function parseDateFromText(string $text): ?Carbon
    {
        $text = $this->normalize($text);
        if (str_contains($text, 'hari ini')) {
            return now()->startOfDay();
        }
        if (str_contains($text, 'besok')) {
            return now()->addDay()->startOfDay();
        }
        if (str_contains($text, 'lusa')) {
            return now()->addDays(2)->startOfDay();
        }

        if (preg_match('#\b(\d{4})[-/](\d{1,2})[-/](\d{1,2})\b#', $text, $matches)) {
            try {
                return Carbon::create((int) $matches[1], (int) $matches[2], (int) $matches[3])->startOfDay();
            } catch (\Throwable $e) {}
        }
        if (preg_match('#\b(\d{1,2})[-/](\d{1,2})[-/](\d{4})\b#', $text, $matches)) {
            try {
                return Carbon::create((int) $matches[3], (int) $matches[2], (int) $matches[1])->startOfDay();
            } catch (\Throwable $e) {}
        }

        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6,
            'jul' => 7, 'agu' => 8, 'sep' => 9, 'okt' => 10, 'nov' => 11, 'des' => 12
        ];

        foreach ($months as $name => $monthNum) {
            if (str_contains($text, $name)) {
                if (preg_match('/(\d{1,2})\s+' . $name . '(?:\s+(\d{4}))?/', $text, $matches)) {
                    $day = (int) $matches[1];
                    $year = isset($matches[2]) ? (int) $matches[2] : (int) now()->year;
                    try {
                        return Carbon::create($year, $monthNum, $day)->startOfDay();
                    } catch (\Throwable $e) {}
                }
            }
        }

        return null;
    }
}
