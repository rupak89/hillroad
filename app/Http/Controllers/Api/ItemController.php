<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemRequest;
use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private ItemService $itemService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ]);

        $items = $this->itemService->getItems(
            (int) ($validated['per_page'] ?? 10),
            $validated['search'] ?? null
        );

        return response()->json([
            'message' => 'List of items',
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemRequest $request)
    {
        $item = $this->itemService->createItem($request->validated());

        return response()->json([
            'message' => 'Item created successfully',
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, string $id)
    {
        $item = $this->itemService->updateItem($id, $request->validated());

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->itemService->deleteItem($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Item deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Item not found or could not be deleted',
        ], 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = $this->itemService->getItem($id);

        if (! $item) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Item details',
            'item' => $item,
        ]);
    }
}
