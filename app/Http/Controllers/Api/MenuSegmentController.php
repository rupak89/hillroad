<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuSegment;
use App\Models\MenuSegmentItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuSegmentController extends Controller
{
    /**
     * Store a newly created segment.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $this->authorize('update', $menu);

        $segment = DB::transaction(function () use ($validated, $menu) {
            $validated['sort_order'] ??= $this->nextSegmentSortOrder($menu->id);

            return MenuSegment::create($validated);
        });
        $segment->load(['items.recipe']);

        return response()->json([
            'message' => 'Segment created successfully',
            'segment' => $segment,
        ], 201);
    }

    /**
     * Update a segment.
     */
    public function update(Request $request, MenuSegment $segment): JsonResponse
    {
        $this->authorize('update', $segment->menu);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $segment->update($validated);
        $segment->load(['items.recipe']);

        return response()->json([
            'message' => 'Segment updated successfully',
            'segment' => $segment,
        ]);
    }

    /**
     * Delete a segment.
     */
    public function destroy(MenuSegment $segment): JsonResponse
    {
        $this->authorize('update', $segment->menu);

        $segment->delete();

        return response()->json([
            'message' => 'Segment deleted successfully',
        ]);
    }

    /**
     * Add an item to a segment.
     */
    public function addItem(Request $request, MenuSegment $segment): JsonResponse
    {
        $this->authorize('update', $segment->menu);

        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $item = DB::transaction(function () use ($validated, $segment) {
            $validated['sort_order'] ??= $this->nextItemSortOrder($segment->id);
            $validated['menu_segment_id'] = $segment->id;

            return MenuSegmentItem::create($validated);
        });
        $item->load(['recipe']);

        return response()->json([
            'message' => 'Item added to segment successfully',
            'item' => $item,
        ], 201);
    }

    /**
     * Update a segment item.
     */
    public function updateItem(Request $request, MenuSegmentItem $item): JsonResponse
    {
        $this->authorize('update', $item->segment->menu);

        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $item->update($validated);
        $item->load(['recipe']);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item,
        ]);
    }

    /**
     * Remove an item from a segment.
     */
    public function removeItem(MenuSegmentItem $item): JsonResponse
    {
        $this->authorize('update', $item->segment->menu);

        $item->delete();

        return response()->json([
            'message' => 'Item removed from segment successfully',
        ]);
    }

    /**
     * Reorder segments within a menu.
     */
    public function reorderSegments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'segments' => 'required|array',
            'segments.*.id' => 'required|exists:menu_segments,id',
            'segments.*.sort_order' => 'required|integer',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $this->authorize('update', $menu);

        DB::transaction(function () use ($validated, $menu) {
            foreach ($validated['segments'] as $segmentData) {
                MenuSegment::where('id', $segmentData['id'])
                    ->where('menu_id', $menu->id)
                    ->update(['sort_order' => $segmentData['sort_order']]);
            }
        });

        return response()->json([
            'message' => 'Segments reordered successfully',
        ]);
    }

    /**
     * Reorder items within a segment.
     */
    public function reorderItems(Request $request, MenuSegment $segment): JsonResponse
    {
        $this->authorize('update', $segment->menu);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_segment_items,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated, $segment) {
            foreach ($validated['items'] as $itemData) {
                MenuSegmentItem::where('id', $itemData['id'])
                    ->where('menu_segment_id', $segment->id)
                    ->update(['sort_order' => $itemData['sort_order']]);
            }
        });

        return response()->json([
            'message' => 'Items reordered successfully',
        ]);
    }

    private function nextSegmentSortOrder(int $menuId): int
    {
        return ((int) MenuSegment::where('menu_id', $menuId)->max('sort_order')) + 1;
    }

    private function nextItemSortOrder(int $segmentId): int
    {
        return ((int) MenuSegmentItem::where('menu_segment_id', $segmentId)->max('sort_order')) + 1;
    }
}
