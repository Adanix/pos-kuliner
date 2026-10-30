<?php

namespace App\Services;

use App\Models\Menu;

class HppService
{
    /**
     * Calculate HPP for a menu item based on its recipe and ingredient avg_costs.
     * Returns array: ['unit_hpp' => int|null, 'hpp_complete' => bool, 'ingredients' => array]
     */
    public function calculateForMenu(Menu $menu): array
    {
        $recipe = $menu->recipe()->with('items.ingredient')->first();

        if (! $recipe || $recipe->items->isEmpty()) {
            return [
                'unit_hpp' => null,
                'hpp_complete' => false,
                'reason' => 'Resep belum dibuat atau bahan kosong',
                'ingredients' => [],
            ];
        }

        $totalHpp = 0.0;
        $isComplete = true;
        $details = [];

        foreach ($recipe->items as $item) {
            $ingredient = $item->ingredient;
            if (! $ingredient) {
                $isComplete = false;
                continue;
            }

            $qtyBase = (float) $item->qty_base;
            $avgCost = (float) $ingredient->avg_cost;

            if ($avgCost <= 0) {
                $isComplete = false;
            }

            $lineCost = $qtyBase * $avgCost;
            $totalHpp += $lineCost;

            $details[] = [
                'ingredient_id' => $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'qty_base' => $qtyBase,
                'unit_cost' => $avgCost,
                'cost_total' => (int) round($lineCost),
            ];
        }

        return [
            'unit_hpp' => $isComplete ? (int) round($totalHpp) : null,
            'hpp_complete' => $isComplete,
            'reason' => $isComplete ? null : 'Ada bahan baku yang belum memiliki riwayat harga beli',
            'ingredients' => $details,
        ];
    }
}
