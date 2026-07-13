<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\Priority;
use App\Models\PriorityKeyword;

class AutoCategoryService
{
    private array $priorityOrder = [
        'low'      => 1,
        'medium'   => 2,
        'high'     => 3,
        'critical' => 4,
    ];

    public function detect(string $text): array
    {
        $clean    = $this->preprocess($text);
        $category = $this->detectCategory($clean);
        $priority = $this->detectPriority($clean, $category['name']);

        return [
            'category_id'   => $category['id'],
            'priority_id'   => $priority['id'],
            'category_name' => $category['name'],
            'priority_name' => $priority['level'],
        ];
    }

    private function preprocess(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        return preg_replace('/\s+/', ' ', trim($text));
    }

    private function isMatch(string $keyword, string $text): bool
    {
        $escaped = preg_quote(strtolower(trim($keyword)), '/');
        $pattern = '/\b' . $escaped . '\b/u';
        return (bool) preg_match($pattern, $text);
    }

    private function detectCategory(string $text): array
    {
        $keywords = CategoryKeyword::with('category')
            ->whereHas('category', fn($q) => $q->where('is_active', true))
            ->get();

        $scores = [];
        foreach ($keywords as $kw) {
            $categoryName = $kw->category->name;
            if (!isset($scores[$categoryName])) {
                $scores[$categoryName] = [
                    'id'            => $kw->category->id,
                    'score'         => 0,
                    'max_priority'  => $kw->category->max_priority ?? 'low',
                    'base_priority' => $kw->category->base_priority ?? 'low',
                ];
            }

            if ($this->isMatch($kw->keyword, $text)) {
                $scores[$categoryName]['score'] += $kw->weight;
            }
        }

        $active = array_filter($scores, fn($s) => $s['score'] > 0);
        if (empty($active)) return $this->getFallback();

        $maxScore = max(array_column($active, 'score'));
        $winners  = array_filter($active, fn($s) => $s['score'] === $maxScore);

        if (count($winners) > 1) return $this->getFallback();

        $winnerName = array_key_first($winners);
        return ['id' => $winners[$winnerName]['id'], 'name' => $winnerName];
    }

    private function detectPriority(string $text, string $categoryName): array
    {
        $category     = Category::where('name', $categoryName)->where('is_active', true)->first();
        $basePriority = $category?->base_priority ?? 'low';
        $maxPriority  = $category?->max_priority  ?? 'high';

        $keywords = PriorityKeyword::with('priority')->get();
        $scores   = [];

        foreach ($keywords as $kw) {
            $level = $kw->priority->level;
            if (!isset($scores[$level])) {
                $scores[$level] = ['id' => $kw->priority_id, 'score' => 0];
            }
            if ($this->isMatch($kw->keyword, $text)) {
                $scores[$level]['score'] += $kw->weight;
            }
        }

        $active = array_filter($scores, fn($s) => $s['score'] > 0);
        if (empty($active)) {
            $priorityModel = Priority::where('level', $basePriority)->first();
            return ['id' => $priorityModel?->id, 'level' => $basePriority];
        }

        uasort($active, fn($a, $b) => $b['score'] <=> $a['score']);

        $winnerLevel = array_key_first($active);
        $winnerId    = $active[$winnerLevel]['id'];

        $winnerOrder = $this->priorityOrder[$winnerLevel] ?? 1;
        $maxOrder    = $this->priorityOrder[$maxPriority]  ?? 3;
        $baseOrder   = $this->priorityOrder[$basePriority] ?? 1;

        $finalOrder = min(max($winnerOrder, $baseOrder), $maxOrder);
        $finalLevel = array_search($finalOrder, $this->priorityOrder) ?: $basePriority;

        if ($finalLevel !== $winnerLevel) {
            $priorityModel = Priority::where('level', $finalLevel)->first();
            $winnerId      = $priorityModel?->id;
        }

        return ['id' => $winnerId, 'level' => $finalLevel];
    }
    private function getFallback(): array
    {
        $general = Category::where('name', 'General')
                           ->where('is_active', true)
                           ->first();

        return [
            'id'   => $general?->id,
            'name' => $general?->name ?? 'General',
        ];
    }
}
