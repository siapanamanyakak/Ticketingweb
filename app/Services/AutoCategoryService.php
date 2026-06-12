<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\Priority;
use App\Models\PriorityKeyword;

class AutoCategoryService
{
    // Urutan level priority untuk perbandingan
    private array $priorityOrder = [
        'low'      => 1,
        'medium'   => 2,
        'high'     => 3,
        'critical' => 4,
    ];

    // ── PUBLIC ENTRY POINT ────────────────────────
    public function detect(string $text): array
    {
        // Step 1: Preprocessing
        $processed = $this->preprocess($text);

        // Step 2 & 3: Detect category & priority secara independen
        $categoryResult  = $this->detectCategory($processed);
        $priorityResult  = $this->detectPriority($processed, $categoryResult['name']);

        return [
            'category_id'   => $categoryResult['id'],
            'priority_id'   => $priorityResult['id'],
            'category_name' => $categoryResult['name'],
            'priority_name' => $priorityResult['level'],
        ];
    }

    // ── STEP 1: TEXT PREPROCESSING ────────────────
    private function preprocess(string $text): array
    {
        // Lowercase
        $text = strtolower($text);

        // Strip punctuation — sisakan hanya alfanumerik dan spasi
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // Normalisasi spasi
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Tokenisasi
        $tokens = explode(' ', $text);
        $tokens = array_filter($tokens, fn($t) => strlen($t) > 0);
        $tokens = array_values($tokens);

        // Anti-space injection — hilangkan semua spasi
        // Untuk deteksi typo spasi seperti "s e r v e r"
        $spaceless = str_replace(' ', '', $text);

        return [
            'original' => $text,
            'tokens'   => $tokens,
            'spaceless'=> $spaceless,
        ];
    }

    // ── STEP 2: MULTI-LAYER MATCHING ─────────────
    private function isMatch(string $keyword, array $processed): bool
    {
        $kwClean    = strtolower(trim($keyword));
        $kwSpaceless = str_replace(' ', '', $kwClean);
        $kwTokens   = explode(' ', $kwClean);

        // Layer 1: Spaceless substring match
        // Deteksi "s e r v e r" → "server"
        if (str_contains($processed['spaceless'], $kwSpaceless)) {
            return true;
        }

        // Layer 2: Exact substring match pada teks asli
        if (str_contains($processed['original'], $kwClean)) {
            return true;
        }

        // Layer 3: Fuzzy/Levenshtein match per token
        // Threshold dinamis: ≤4 karakter → toleransi 1, >4 karakter → toleransi 2
if (count($kwTokens) === 1) {
            $kwToken = $kwTokens[0];
            $threshold  = strlen($kwToken) <= 4 ? 1 : 2;

            foreach ($processed['tokens'] as $token) {
                $distance = levenshtein($token, $kwToken);
                if ($distance <= $threshold) {
                    return true;
                }
            }
        }

        return false;
    }

    // ── STEP 3A: DETECT CATEGORY ─────────────────
    private function detectCategory(array $processed): array
    {
        $keywords = CategoryKeyword::with('category')
            ->whereHas('category', fn($q) => $q->where('is_active', true))
            ->get();

        // Accumulator array
        $scores = [];

        foreach ($keywords as $kw) {
            $categoryName = $kw->category->name;
            $categoryId   = $kw->category->id;

            if (!isset($scores[$categoryName])) {
                $scores[$categoryName] = ['id' => $categoryId, 'score' => 0];
            }

            // Multi-layer matching
            if ($this->isMatch($kw->keyword, $processed)) {
                $scores[$categoryName]['score'] += $kw->weight;
            }
        }

        // Filter hanya yang punya score > 0
        $scores = array_filter($scores, fn($s) => $s['score'] > 0);

        if (empty($scores)) {
            // Fallback ke Other
            $other = Category::where('name', 'Other')
                             ->where('is_active', true)
                             ->first();
            return ['id' => $other?->id, 'name' => 'Other'];
        }

        // Sort descending by score
        uasort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        $winner     = array_key_first($scores);
        $winnerId   = $scores[$winner]['id'];

        return ['id' => $winnerId, 'name' => $winner];
    }

    // ── STEP 3B: DETECT PRIORITY ─────────────────
    private function detectPriority(array $processed, string $categoryName): array
    {
        // Ambil base & max priority dari kategori
        $category     = Category::where('name', $categoryName)
                                ->where('is_active', true)
                                ->first();
        $basePriority = $category?->base_priority ?? 'low';
        $maxPriority  = $category?->max_priority  ?? 'high';

        // Ambil semua priority keywords dengan bobot
        $keywords = PriorityKeyword::with('priority')->get();

        // Accumulator per level
        $scores = [];

        foreach ($keywords as $kw) {
            $level = $kw->priority->level;

            if (!isset($scores[$level])) {
                $scores[$level] = ['id' => $kw->priority_id, 'score' => 0];
            }

            // Multi-layer matching — scan SEMUA keyword (tidak early return)
            if ($this->isMatch($kw->keyword, $processed)) {
                $scores[$level]['score'] += $kw->weight;
            }
        }

        // Filter yang punya score > 0
        $activeScores = array_filter($scores, fn($s) => $s['score'] > 0);

        // Kalau tidak ada match → pakai base priority
        if (empty($activeScores)) {
            $priorityModel = Priority::where('level', $basePriority)->first();
            return ['id' => $priorityModel?->id, 'level' => $basePriority];
        }

        // Sort descending by score
        uasort($activeScores, fn($a, $b) => $b['score'] <=> $a['score']);

        $winnerLevel = array_key_first($activeScores);
        $winnerId    = $activeScores[$winnerLevel]['id'];

        // Step 4: Ceiling validation
        $winnerOrder = $this->priorityOrder[$winnerLevel] ?? 1;
        $maxOrder    = $this->priorityOrder[$maxPriority]  ?? 3;
        $baseOrder   = $this->priorityOrder[$basePriority] ?? 1;

        // Ambil yang lebih tinggi antara winner dan base
        $finalOrder = max($winnerOrder, $baseOrder);

        // Tapi tidak boleh melebihi max_priority kategori
        $finalOrder = min($finalOrder, $maxOrder);

        // Kembalikan level dari order
        $finalLevel = array_search($finalOrder, $this->priorityOrder) ?: $basePriority;

        // Kalau final level berbeda dari winner, ambil ID yang benar
        if ($finalLevel !== $winnerLevel) {
            $priorityModel = Priority::where('level', $finalLevel)->first();
            $winnerId      = $priorityModel?->id;
        }

        return ['id' => $winnerId, 'level' => $finalLevel];
    }
}
