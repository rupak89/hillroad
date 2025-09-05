<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuSegment;
use App\Models\MenuSegmentItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuSegmentController extends Controller
{
    /**
     * Store a newly created segment
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        // Check menu ownership
        $menu = Menu::findOrFail($validated['menu_id']);
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = MenuSegment::where('menu_id', $validated['menu_id'])
            ->max('sort_order') + 1;
        }

        $segment = MenuSegment::create($validated);
        $segment->load(['items.recipe']);

        return response()->json([
            'message' => 'Segment created successfully',
            'segment' => $segment,
        ], 201);
    }

    /**
     * Update a segment
     */
    public function update(Request $request, MenuSegment $segment): JsonResponse
    {
        // Check menu ownership
        if ($segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
     * Delete a segment
     */
    public function destroy(Request $request, MenuSegment $segment): JsonResponse
    {
        // Check menu ownership
        if ($segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $segment->delete();

        return response()->json([
            'message' => 'Segment deleted successfully',
        ]);
    }

    /**
     * Add item to segment
     */
    public function addItem(Request $request, MenuSegment $segment): JsonResponse
    {
        // Check menu ownership
        if ($segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'quantity' => 'required|numeric|min:0.01',
            'quantity_type' => 'required|in:per_guest,total',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = MenuSegmentItem::where('menu_segment_id', $segment->id)->max('sort_order') + 1;
        }

        $validated['menu_segment_id'] = $segment->id;

        $item = MenuSegmentItem::create($validated);
        $item->load(['recipe']);

        return response()->json([
            'message' => 'Item added to segment successfully',
            'item' => $item,
        ], 201);
    }

    /**
     * Update segment item
     */
    public function updateItem(Request $request, MenuSegmentItem $item): JsonResponse
    {
        // Check menu ownership
        if ($item->segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'quantity' => 'required|numeric|min:0.01',
            'quantity_type' => 'required|in:per_guest,total',
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
     * Remove item from segment
     */
    public function removeItem(Request $request, MenuSegmentItem $item): JsonResponse
    {
        // Check menu ownership
        if ($item->segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed from segment successfully',
        ]);
    }

    /**
     * Reorder segments
     */
    public function reorderSegments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'segments' => 'required|array',
            'segments.*.id' => 'required|exists:menu_segments,id',
            'segments.*.sort_order' => 'required|integer',
        ]);

        // Check menu ownership
        $menu = Menu::findOrFail($validated['menu_id']);
        if ($menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        foreach ($validated['segments'] as $segmentData) {
            MenuSegment::where('id', $segmentData['id'])
                ->where('menu_id', $validated['menu_id'])
                ->update(['sort_order' => $segmentData['sort_order']]);
        }

        return response()->json([
            'message' => 'Segments reordered successfully',
        ]);
    }

    /**
     * Reorder items within a segment
     */
    public function reorderItems(Request $request, MenuSegment $segment): JsonResponse
    {
        // Check menu ownership
        if ($segment->menu->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_segment_items,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            MenuSegmentItem::where('id', $itemData['id'])
                ->where('menu_segment_id', $segment->id)
                ->update(['sort_order' => $itemData['sort_order']]);
        }

        return response()->json([
            'message' => 'Items reordered successfully',
        ]);
    }
}
