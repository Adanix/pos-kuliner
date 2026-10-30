<?php

namespace App\Services\Ai;

use App\Models\Menu;
use App\Models\MenuAlias;

class MenuMatcher
{
    /**
     * Normalize food item string for matching.
     */
    public function normalize(string $name): string
    {
        $name = strtolower($name);
        // Remove common promo tokens
        $name = preg_replace('/\b(promo|diskon|gratis|free|paket|hemat)\b/i', '', $name);
        // Remove price patterns like "25k", "25.000", "rp 25000"
        $name = preg_replace('/(rp\s*|\b)\d+[\.,]?\d*\s*(k|rb)?\b/i', '', $name);
        // Remove punctuation and extra whitespace
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    /**
     * Match a screenshot item against master menu and aliases.
     * Returns: ['menu_id' => int|null, 'menu' => Menu|null, 'confidence' => float, 'match_type' => string, 'suggestions' => array]
     */
    public function matchItem(string $sourceName, ?string $channel = null): array
    {
        $normalized = $this->normalize($sourceName);
        $allActiveMenus = Menu::where('is_active', true)->get();

        // 1. Exact Match on Master Menu Name
        foreach ($allActiveMenus as $menu) {
            if ($this->normalize($menu->name) === $normalized) {
                return [
                    'menu_id' => $menu->id,
                    'menu' => $menu,
                    'confidence' => 1.0,
                    'match_type' => 'exact',
                    'suggestions' => [],
                ];
            }
        }

        // 2. Check Menu Aliases
        $aliasQuery = MenuAlias::where('normalized_name', $normalized);
        if ($channel) {
            $aliasQuery->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhereNull('channel');
            });
        }
        $alias = $aliasQuery->first();
        if ($alias && $alias->menu && $alias->menu->is_active) {
            return [
                'menu_id' => $alias->menu_id,
                'menu' => $alias->menu,
                'confidence' => 0.95,
                'match_type' => 'alias',
                'suggestions' => [],
            ];
        }

        // 3. Fuzzy Matching
        $bestMatch = null;
        $highestSim = 0.0;
        $suggestions = [];

        foreach ($allActiveMenus as $menu) {
            $menuNorm = $this->normalize($menu->name);
            similar_text($normalized, $menuNorm, $percent);
            $sim = $percent / 100.0;

            if ($sim >= 0.70) {
                $suggestions[] = [
                    'menu_id' => $menu->id,
                    'name' => $menu->name,
                    'price' => $menu->price,
                    'similarity' => round($sim, 2),
                ];
            }

            if ($sim > $highestSim) {
                $highestSim = $sim;
                $bestMatch = $menu;
            }
        }

        // Sort suggestions descending
        usort($suggestions, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        if ($highestSim >= 0.85 && $bestMatch) {
            return [
                'menu_id' => $bestMatch->id,
                'menu' => $bestMatch,
                'confidence' => round($highestSim, 2),
                'match_type' => 'fuzzy_auto',
                'suggestions' => $suggestions,
            ];
        }

        return [
            'menu_id' => null,
            'menu' => null,
            'confidence' => round($highestSim, 2),
            'match_type' => 'unmatched',
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Save confirmed alias from human review.
     */
    public function saveAlias(int $menuId, string $sourceName, ?string $channel = null): void
    {
        $normalized = $this->normalize($sourceName);
        if ($normalized === '') {
            return;
        }

        MenuAlias::updateOrCreate(
            [
                'channel' => $channel,
                'normalized_name' => $normalized,
            ],
            [
                'menu_id' => $menuId,
                'source_name' => $sourceName,
            ]
        );
    }
}
