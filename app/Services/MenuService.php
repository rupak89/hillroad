<?php

namespace App\Services;

use App\DTOs\Menu\CreateMenuDTO;
use App\DTOs\Menu\UpdateMenuDTO;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuService
{
    /**
     * Get paginated menus for a user
     */
    public function getMenusForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Menu::with([
                'segments' => function($query) {
                    $query->orderBy('sort_order');
                },
                'segments.items' => function($query) {
                    $query->orderBy('sort_order');
                },
                'segments.items.recipe:id,recipe_name'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new menu
     */
    public function createMenu(CreateMenuDTO $dto): Menu
    {
        // Create the menu first
        $menu = Menu::create([
            'name' => $dto->name,
            'description' => $dto->description,
            'target_head_count' => $dto->targetHeadCount,
            'markup_percentage' => $dto->markupPercentage,
            'user_id' => $dto->userId,
        ]);

        // Create segments and items from the DTO
        foreach ($dto->segments as $segmentDTO) {
            $segment = $menu->segments()->create([
                'name' => $segmentDTO->name,
                'sort_order' => $segmentDTO->sortOrder,
            ]);

            // Create items for this segment
            foreach ($segmentDTO->items as $itemDTO) {
                $segment->items()->create([
                    'recipe_id' => $itemDTO->recipeId,
                    'quantity' => $itemDTO->quantity,
                    'notes' => $itemDTO->notes,
                ]);
            }
        }

        return $this->loadMenuRelations($menu);
    }

    /**
     * Update an existing menu
     */
    public function updateMenu(Menu $menu, UpdateMenuDTO $dto): Menu
    {
        // Update basic menu info
        $menu->update([
            'name' => $dto->name,
            'description' => $dto->description,
            'target_head_count' => $dto->targetHeadCount,
            'markup_percentage' => $dto->markupPercentage,
        ]);

        // Get existing segment and item IDs to track what to keep/delete
        $existingSegmentIds = $menu->segments->pluck('id')->toArray();
        $submittedSegmentIds = [];

        // Process segments
        foreach ($dto->segments as $segmentDTO) {
            if ($segmentDTO->id) {
                // Update existing segment
                $segment = $menu->segments()->find($segmentDTO->id);
                if ($segment) {
                    $segment->update([
                        'name' => $segmentDTO->name,
                        'sort_order' => $segmentDTO->sortOrder,
                    ]);
                    $submittedSegmentIds[] = $segmentDTO->id;
                }
            } else {
                // Create new segment
                $segment = $menu->segments()->create([
                    'name' => $segmentDTO->name,
                    'sort_order' => $segmentDTO->sortOrder,
                ]);
                $submittedSegmentIds[] = $segment->id;
            }

            // Process items for this segment
            $existingItemIds = $segment->items->pluck('id')->toArray();
            $submittedItemIds = [];

            foreach ($segmentDTO->items as $itemDTO) {
                if ($itemDTO->id) {
                    // Update existing item
                    $item = $segment->items()->find($itemDTO->id);
                    if ($item) {
                        $item->update([
                            'recipe_id' => $itemDTO->recipeId,
                            'quantity' => $itemDTO->quantity,
                            'notes' => $itemDTO->notes,
                        ]);
                        $submittedItemIds[] = $itemDTO->id;
                    }
                } else {
                    // Create new item
                    $item = $segment->items()->create([
                        'recipe_id' => $itemDTO->recipeId,
                        'quantity' => $itemDTO->quantity,
                        'notes' => $itemDTO->notes,
                    ]);
                    $submittedItemIds[] = $item->id;
                }
            }

            // Delete items that were removed
            $itemsToDelete = array_diff($existingItemIds, $submittedItemIds);
            if (!empty($itemsToDelete)) {
                $segment->items()->whereIn('id', $itemsToDelete)->delete();
            }
        }

        // Delete segments that were removed
        $segmentsToDelete = array_diff($existingSegmentIds, $submittedSegmentIds);
        if (!empty($segmentsToDelete)) {
            $menu->segments()->whereIn('id', $segmentsToDelete)->delete();
        }

        return $this->loadMenuRelations($menu);
    }

    /**
     * Get menu with all relations loaded
     */
    public function getMenuWithRelations(Menu $menu): Menu
    {
        return $this->loadMenuRelations($menu);
    }

    /**
     * Duplicate a menu
     */
    public function duplicateMenu(Menu $menu): Menu
    {
        $duplicatedMenu = $menu->replicate();
        $duplicatedMenu->name = $menu->name . ' (Copy)';
        $duplicatedMenu->save();

        // Duplicate segments and items
        foreach ($menu->segments as $segment) {
            $duplicatedSegment = $segment->replicate();
            $duplicatedSegment->menu_id = $duplicatedMenu->id;
            $duplicatedSegment->save();

            foreach ($segment->items as $item) {
                $duplicatedItem = $item->replicate();
                $duplicatedItem->menu_segment_id = $duplicatedSegment->id;
                $duplicatedItem->save();
            }
        }

        return $this->loadMenuRelations($duplicatedMenu);
    }

    /**
     * Get cost breakdown for a menu
     */
    public function getCostBreakdown(Menu $menu): array
    {
        $menu = $this->loadMenuRelations($menu);

        $breakdown = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'target_head_count' => $menu->target_head_count,
            'markup_percentage' => $menu->markup_percentage,
            'segments' => [],
            'totals' => [
                'base_cost' => $menu->total_cost,
                'cost_per_person' => $menu->cost_per_person,
                'markup_amount_per_person' => $menu->markup_amount_per_person,
                'selling_price_per_person' => $menu->selling_price_per_person,
                'total_markup_amount' => $menu->total_markup_amount,
                'total_selling_price' => $menu->total_selling_price,
            ]
        ];

        foreach ($menu->segments as $segment) {
            $segmentData = [
                'id' => $segment->id,
                'name' => $segment->name,
                'total_cost' => $segment->total_cost,
                'items' => []
            ];

            foreach ($segment->items as $item) {
                $segmentData['items'][] = [
                    'id' => $item->id,
                    'recipe_name' => $item->recipe->recipe_name ?? 'Unknown Recipe',
                    'quantity' => $item->quantity,
                    'formatted_quantity' => $item->formatted_quantity,
                    'cost_per_unit' => $item->cost_per_unit,
                    'total_cost' => $item->total_cost,
                    'notes' => $item->notes,
                ];
            }

            $breakdown['segments'][] = $segmentData;
        }

        return $breakdown;
    }

    /**
     * Get printable menu data for customers
     */
    public function getPrintableMenu(Menu $menu): array
    {
        $menu = $this->loadMenuRelations($menu);

        // Calculate markup multiplier
        $markupMultiplier = 1 + ($menu->markup_percentage / 100);

        $printableMenu = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'description' => $menu->description,
            'target_head_count' => $menu->target_head_count,
            'segments' => [],
            'pricing' => [
                'price_per_person' => $menu->selling_price_per_person,
                'total_price' => $menu->total_selling_price,
                'formatted_price_per_person' => '$' . number_format($menu->selling_price_per_person, 2),
                'formatted_total_price' => '$' . number_format($menu->total_selling_price, 2),
            ]
        ];

        foreach ($menu->segments as $segment) {
            $segmentData = [
                'id' => $segment->id,
                'name' => $segment->name,
                'items' => []
            ];

            foreach ($segment->items as $item) {
                // Calculate selling price for this item (base cost + markup)
                $baseCostPerItem = $item->total_cost;
                $sellingPricePerItem = $baseCostPerItem * $markupMultiplier;

                $segmentData['items'][] = [
                    'id' => $item->id,
                    'recipe_name' => $item->recipe->recipe_name ?? 'Unknown Recipe',
                    'quantity' => $item->quantity,
                    'formatted_quantity' => $item->formatted_quantity,
                    'price_per_person' => $sellingPricePerItem,
                    'formatted_price_per_person' => '$' . number_format($sellingPricePerItem, 2),
                    'total_price' => $sellingPricePerItem * $menu->target_head_count,
                    'formatted_total_price' => '$' . number_format($sellingPricePerItem * $menu->target_head_count, 2),
                    'notes' => $item->notes,
                ];
            }

            $printableMenu['segments'][] = $segmentData;
        }

        return $printableMenu;
    }

    /**
     * Load menu relationships consistently
     */
    private function loadMenuRelations(Menu $menu): Menu
    {
        return $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);
    }
}
