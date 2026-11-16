<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Costs are exposed with explicit names so clients never have to guess
     * whether a value is per guest or for the whole event.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $menu = $this->resource;

        $menu->loadMissing(['segments.items.recipe:id,recipe_name,servings']);

        $costPerGuest = round($menu->cost_per_guest, 2);
        $totalEventCost = round($menu->total_event_cost, 2);

        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'description' => $menu->description,
            'target_head_count' => (int) $menu->target_head_count,
            'markup_percentage' => (float) $menu->markup_percentage,

            // Per-guest values.
            'cost_per_guest' => $costPerGuest,
            'cost_per_person' => round($menu->cost_per_person, 2),
            'markup_amount_per_guest' => round($menu->markup_amount_per_guest, 2),
            'markup_amount_per_person' => round($menu->markup_amount_per_person, 2),
            'selling_price_per_guest' => round($menu->selling_price_per_guest, 2),
            'selling_price_per_person' => round($menu->selling_price_per_person, 2),

            // Values for the whole event.
            'total_event_cost' => $totalEventCost,
            // Legacy field: historically this was the per-guest cost. It is
            // now the event total; new clients should use the explicit fields.
            'total_cost' => $totalEventCost,
            'total_markup_amount' => round($menu->total_markup_amount, 2),
            'total_selling_price' => round($menu->total_selling_price, 2),

            'segments_count' => $menu->segments->count(),
            'segments' => $menu->segments
                ->map(fn ($segment) => [
                    'id' => $segment->id,
                    'name' => $segment->name,
                    'sort_order' => $segment->sort_order,
                    'items' => $segment->items
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'recipe_id' => $item->recipe_id,
                            'recipe_name' => $item->recipe?->recipe_name,
                            'quantity' => (float) $item->quantity,
                            'notes' => $item->notes,
                            'sort_order' => $item->sort_order,
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
            'created_at' => $menu->created_at?->toIso8601String(),
            'updated_at' => $menu->updated_at?->toIso8601String(),
        ];
    }
}
