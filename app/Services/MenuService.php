<?php

namespace App\Services;

use App\DTOs\Menu\CreateMenuDTO;
use App\DTOs\Menu\UpdateMenuDTO;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MenuService
{
    /**
     * Get paginated menus for a user
     */
    public function getMenusForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Menu::with([
            'segments' => function ($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function ($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name,servings',
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
        return DB::transaction(function () use ($dto) {
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
        });
    }

    /**
     * Update an existing menu
     */
    public function updateMenu(Menu $menu, UpdateMenuDTO $dto): Menu
    {
        return DB::transaction(function () use ($menu, $dto) {
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
                if (! empty($itemsToDelete)) {
                    $segment->items()->whereIn('id', $itemsToDelete)->delete();
                }
            }

            // Delete segments that were removed
            $segmentsToDelete = array_diff($existingSegmentIds, $submittedSegmentIds);
            if (! empty($segmentsToDelete)) {
                $menu->segments()->whereIn('id', $segmentsToDelete)->delete();
            }

            return $this->loadMenuRelations($menu);
        });
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
        return DB::transaction(function () use ($menu) {
            $duplicatedMenu = $menu->replicate();
            $duplicatedMenu->name = $menu->name.' (Copy)';
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
        });
    }

    /**
     * Get cost breakdown for a menu
     */
    public function getCostBreakdown(Menu $menu): array
    {
        $menu = $this->loadMenuRelations($menu);

        $costPerGuest = round($menu->cost_per_guest, 2);
        $totalEventCost = round($menu->total_event_cost, 2);
        $gst = app(GstService::class);

        $breakdown = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'target_head_count' => $menu->target_head_count,
            'markup_percentage' => $menu->markup_percentage,
            'gst' => [
                'registered' => $gst->isRegistered(),
                'rate' => $gst->rate(),
                'prices_include_gst' => $gst->pricesIncludeGst(),
            ],
            'segments' => [],
            'totals' => [
                // Per-guest values. Costs are GST-exclusive; selling prices
                // are shown both ways because customers pay the inclusive one.
                'base_cost' => $costPerGuest,
                'cost_per_guest' => $costPerGuest,
                'cost_per_person' => round($menu->cost_per_person, 2),
                'cost_gst_per_guest' => round($menu->cost_gst_per_guest, 2),
                'cost_per_guest_incl_gst' => round($menu->cost_per_guest_incl_gst, 2),
                'markup_amount_per_guest' => round($menu->markup_amount_per_guest, 2),
                'markup_amount_per_person' => round($menu->markup_amount_per_person, 2),
                'selling_price_per_guest' => round($menu->selling_price_per_guest, 2),
                'selling_price_per_person' => round($menu->selling_price_per_person, 2),
                'selling_gst_per_guest' => round($menu->selling_gst_per_guest, 2),
                'selling_price_per_guest_incl_gst' => round($menu->selling_price_per_guest_incl_gst, 2),
                // Values for the whole event.
                'total_event_cost' => $totalEventCost,
                // Legacy key: the UI used to read this as the event total.
                'total_cost' => $totalEventCost,
                'total_cost_gst' => round($menu->total_cost_gst, 2),
                'total_event_cost_incl_gst' => round($menu->total_event_cost_incl_gst, 2),
                'total_markup_amount' => round($menu->total_markup_amount, 2),
                'total_selling_price' => round($menu->total_selling_price, 2),
                'total_selling_gst' => round($menu->total_selling_gst, 2),
                'total_selling_price_incl_gst' => round($menu->total_selling_price_incl_gst, 2),
            ],
        ];

        foreach ($menu->segments as $segment) {
            $segmentData = [
                'id' => $segment->id,
                'name' => $segment->name,
                'total_cost' => round($segment->total_cost, 2),
                'total_cost_incl_gst' => round($gst->amountIncludingGst($segment->total_cost), 2),
                'items' => [],
            ];

            foreach ($segment->items as $item) {
                $segmentData['items'][] = [
                    'id' => $item->id,
                    'recipe_name' => $item->recipe->recipe_name ?? 'Unknown Recipe',
                    'quantity' => $item->quantity,
                    'formatted_quantity' => $item->formatted_quantity,
                    'cost_per_unit' => round($item->cost_per_unit, 2),
                    'cost_per_unit_incl_gst' => round($gst->amountIncludingGst($item->cost_per_unit), 2),
                    'total_cost' => round($item->total_cost, 2),
                    'total_cost_incl_gst' => round($gst->amountIncludingGst($item->total_cost), 2),
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
        $gst = app(GstService::class);
        $gstRate = $gst->rate();

        $pricePerPersonExGst = round($menu->selling_price_per_guest, 2);
        $pricePerPersonInclGst = round($menu->selling_price_per_guest_incl_gst, 2);
        $totalPriceExGst = round($menu->total_selling_price, 2);
        $totalPriceInclGst = round($menu->total_selling_price_incl_gst, 2);
        $gstPerPerson = round($pricePerPersonInclGst - $pricePerPersonExGst, 2);
        $totalGst = round($totalPriceInclGst - $totalPriceExGst, 2);

        $printableMenu = [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'description' => $menu->description,
            'target_head_count' => $menu->target_head_count,
            'segments' => [],
            'pricing' => [
                // Customer-facing prices are GST-inclusive in New Zealand.
                'price_per_person' => $pricePerPersonInclGst,
                'price_per_guest' => $pricePerPersonInclGst,
                'total_price' => $totalPriceInclGst,
                'price_per_person_excl_gst' => $pricePerPersonExGst,
                'total_price_excl_gst' => $totalPriceExGst,
                'formatted_price_per_person' => $this->formatMoney($pricePerPersonInclGst),
                'formatted_price_per_guest' => $this->formatMoney($pricePerPersonInclGst),
                'formatted_total_price' => $this->formatMoney($totalPriceInclGst),
                'formatted_price_per_person_excl_gst' => $this->formatMoney($pricePerPersonExGst),
                'gst' => [
                    'registered' => $gst->isRegistered(),
                    'rate' => $gstRate,
                    'amount_per_person' => $gstPerPerson,
                    'amount_total' => $totalGst,
                    'formatted_amount_per_person' => $this->formatMoney($gstPerPerson),
                    'formatted_amount_total' => $this->formatMoney($totalGst),
                    'note' => $gst->isRegistered()
                        ? 'All prices include GST at '.$this->formatGstRate($gstRate).'%.'
                        : 'GST is not applicable.',
                ],
            ],
        ];

        foreach ($menu->segments as $segment) {
            $segmentData = [
                'id' => $segment->id,
                'name' => $segment->name,
                'items' => [],
            ];

            foreach ($segment->items as $item) {
                // Calculate selling price for this item (base cost + markup),
                // then add GST for the customer-facing price. Keep the
                // unrounded inclusive amount for the line total so the printed
                // item totals add up to the event total.
                $sellingPricePerItemExGstExact = $item->total_cost * $markupMultiplier;
                $sellingPricePerItemExGst = round($sellingPricePerItemExGstExact, 2);
                $sellingPricePerItemInclExact = $gst->amountIncludingGst($sellingPricePerItemExGstExact);
                $sellingPricePerItem = round($sellingPricePerItemInclExact, 2);
                $totalSellingPrice = round($sellingPricePerItemInclExact * $menu->target_head_count, 2);

                $segmentData['items'][] = [
                    'id' => $item->id,
                    'recipe_name' => $item->recipe->recipe_name ?? 'Unknown Recipe',
                    'quantity' => $item->quantity,
                    'formatted_quantity' => $item->formatted_quantity,
                    'price_per_person' => $sellingPricePerItem,
                    'price_per_person_excl_gst' => $sellingPricePerItemExGst,
                    'formatted_price_per_person' => $this->formatMoney($sellingPricePerItem),
                    'formatted_price_per_person_excl_gst' => $this->formatMoney($sellingPricePerItemExGst),
                    'total_price' => $totalSellingPrice,
                    'formatted_total_price' => $this->formatMoney($totalSellingPrice),
                    'notes' => $item->notes,
                ];
            }

            $printableMenu['segments'][] = $segmentData;
        }

        return $printableMenu;
    }

    /**
     * Load menu relationships consistently.
     *
     * `servings` must be selected because recipe cost is per serving.
     */
    private function loadMenuRelations(Menu $menu): Menu
    {
        return $menu->load([
            'segments' => function ($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function ($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name,servings',
        ]);
    }

    private function formatMoney(float|int|string|null $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }

    private function formatGstRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }
}
