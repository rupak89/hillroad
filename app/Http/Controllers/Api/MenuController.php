<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);

        $menus = Menu::with([
                'segments' => function($query) {
                    $query->orderBy('sort_order');
                },
                'segments.items' => function($query) {
                    $query->orderBy('sort_order');
                },
                'segments.items.recipe:id,recipe_name'
            ])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'message' => 'List of menus',
            'menus' => $menus,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_head_count' => 'required|integer|min:1',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'segments' => 'required|array|min:1',
            'segments.*.name' => 'required|string|max:255',
            'segments.*.sort_order' => 'nullable|integer',
            'segments.*.items' => 'required|array|min:1',
            'segments.*.items.*.recipe_id' => 'required|exists:recipes,id',
            'segments.*.items.*.quantity' => 'required|numeric|min:0.01',
            'segments.*.items.*.notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        // Create the menu first
        $menu = Menu::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'target_head_count' => $validated['target_head_count'],
            'markup_percentage' => $validated['markup_percentage'] ?? 0,
            'user_id' => $validated['user_id'],
        ]);

        // Create segments and items from the frontend data
        foreach ($validated['segments'] as $index => $segmentData) {
            $segment = $menu->segments()->create([
                'name' => $segmentData['name'],
                'sort_order' => $segmentData['sort_order'] ?? ($index + 1),
            ]);

            // Create items for this segment
            foreach ($segmentData['items'] as $itemData) {
                // Only create items that have a recipe_id and quantity
                if (!empty($itemData['recipe_id']) && !empty($itemData['quantity'])) {
                    $segment->items()->create([
                        'recipe_id' => $itemData['recipe_id'],
                        'quantity' => $itemData['quantity'],
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }
        }

        // Load the menu with relationships
        $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

        return response()->json([
            'message' => 'Menu created successfully',
            'menu' => $menu,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

        return response()->json([
            'message' => 'Menu details',
            'menu' => $menu,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_head_count' => 'required|integer|min:1',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'segments' => 'required|array|min:1',
            'segments.*.id' => 'nullable|exists:menu_segments,id',
            'segments.*.name' => 'required|string|max:255',
            'segments.*.sort_order' => 'nullable|integer',
            'segments.*.items' => 'required|array|min:1',
            'segments.*.items.*.id' => 'nullable|exists:menu_segment_items,id',
            'segments.*.items.*.recipe_id' => 'required|exists:recipes,id',
            'segments.*.items.*.quantity' => 'required|numeric|min:0.01',
            'segments.*.items.*.notes' => 'nullable|string',
        ]);

        // Update basic menu info
        $menu->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'target_head_count' => $validated['target_head_count'],
            'markup_percentage' => $validated['markup_percentage'] ?? 0,
        ]);

        // Get existing segment and item IDs to track what to keep/delete
        $existingSegmentIds = $menu->segments->pluck('id')->toArray();
        $submittedSegmentIds = [];

        // Process segments
        foreach ($validated['segments'] as $index => $segmentData) {
            if (isset($segmentData['id'])) {
                // Update existing segment
                $segment = $menu->segments()->find($segmentData['id']);
                if ($segment) {
                    $segment->update([
                        'name' => $segmentData['name'],
                        'sort_order' => $segmentData['sort_order'] ?? ($index + 1),
                    ]);
                    $submittedSegmentIds[] = $segmentData['id'];
                }
            } else {
                // Create new segment
                $segment = $menu->segments()->create([
                    'name' => $segmentData['name'],
                    'sort_order' => $segmentData['sort_order'] ?? ($index + 1),
                ]);
                $submittedSegmentIds[] = $segment->id;
            }

            // Process items for this segment
            $existingItemIds = $segment->items->pluck('id')->toArray();
            $submittedItemIds = [];

            foreach ($segmentData['items'] as $itemData) {
                if (!empty($itemData['recipe_id']) && !empty($itemData['quantity'])) {
                    if (isset($itemData['id'])) {
                        // Update existing item
                        $item = $segment->items()->find($itemData['id']);
                        if ($item) {
                            $item->update([
                                'recipe_id' => $itemData['recipe_id'],
                                'quantity' => $itemData['quantity'],
                                'notes' => $itemData['notes'] ?? null,
                            ]);
                            $submittedItemIds[] = $itemData['id'];
                        }
                    } else {
                        // Create new item
                        $item = $segment->items()->create([
                            'recipe_id' => $itemData['recipe_id'],
                            'quantity' => $itemData['quantity'],
                            'notes' => $itemData['notes'] ?? null,
                        ]);
                        $submittedItemIds[] = $item->id;
                    }
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

        $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

        return response()->json([
            'message' => 'Menu updated successfully',
            'menu' => $menu,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $menu->delete();

        return response()->json([
            'message' => 'Menu deleted successfully',
        ]);
    }

    /**
     * Duplicate a menu
     */
    public function duplicate(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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

        $duplicatedMenu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

        return response()->json([
            'message' => 'Menu duplicated successfully',
            'menu' => $duplicatedMenu,
        ], 201);
    }

    /**
     * Get menu cost breakdown
     */
    public function costBreakdown(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

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

        return response()->json([
            'message' => 'Menu cost breakdown',
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Get customer-friendly printable menu
     */
    public function printableMenu(Request $request, Menu $menu): JsonResponse
    {
        // Check ownership
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $menu->load([
            'segments' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items' => function($query) {
                $query->orderBy('sort_order');
            },
            'segments.items.recipe:id,recipe_name'
        ]);

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

        return response()->json([
            'message' => 'Customer printable menu',
            'menu' => $printableMenu,
        ]);
    }
}
