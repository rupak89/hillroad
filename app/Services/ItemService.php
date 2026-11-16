<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemService
{
    /**
     * Get the list of items with pagination.
     */
    public function getItems(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = Item::with(['supplier', 'defaultBrand', 'group', 'orderingUnit', 'countingUnit']);

        if ($search) {
            $query->where('item_name', 'LIKE', '%'.$search.'%');
        }

        return $query->orderBy('item_name')
            ->paginate($perPage);
    }

    /**
     * Create a new item.
     *
     * @throws \Exception
     */
    public function createItem(array $data): Item
    {
        try {
            return DB::transaction(fn () => Item::create($data));
        } catch (\Exception $e) {
            Log::error('Error creating item: '.$e->getMessage(), [
                'data' => $data,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing item.
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function updateItem(int $id, array $data): Item
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $item = Item::findOrFail($id);
                $item->update($data);

                return $item->fresh();
            });
        } catch (ModelNotFoundException $e) {
            Log::error('Item not found for update: '.$id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating item: '.$e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Delete an item.
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function deleteItem(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $item = Item::findOrFail($id);

                return $item->delete();
            });
        } catch (ModelNotFoundException $e) {
            Log::error('Item not found for deletion: '.$id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error deleting item: '.$e->getMessage(), [
                'id' => $id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Get a single item by ID.
     *
     * @throws ModelNotFoundException
     */
    public function getItem(int $id): Item
    {
        try {
            return Item::with(['supplier', 'defaultBrand', 'group', 'orderingUnit', 'countingUnit'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error('Item not found: '.$id);
            throw $e;
        }
    }
}
