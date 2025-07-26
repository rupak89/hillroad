<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ItemService;
use App\Http\Requests\ItemRequest;

class ItemController extends Controller
{

    function __construct(private ItemService $itemService)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $items = $this->itemService->getItems($perPage);

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
        $unit = $this->itemService->createItem($request->validated());

        return response()->json([
            'message' => 'Item created successfully',
            'unit' => $unit,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ItemRequest $request, string $id)
    {
        $unit = $this->itemService->updateItem($id, $request->validated());

        return response()->json([
            'message' => 'Item updated successfully',
            'unit' => $unit,
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

        if (!$item) {
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
