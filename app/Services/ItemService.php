<?php

namespace App\Services;

use App\Models\Item;
use App\Http\Resources\ItemResource;
use App\DTOs\CreateItemDTO;
use App\DTOs\UpdateItemDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemService
{
    /**
     * Get the list of items with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getItems(int $perPage = 10): LengthAwarePaginator
    {
        return Item::with(['supplier', 'defaultBrand', 'group', 'orderingUnit', 'countingUnit'])
            ->orderBy('item_name')
            ->paginate($perPage);
    }

    /**
     * Create a new item.
     *
     * @param array $data
     * @return Item
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function createItem(array $data): Item
    {
        try {
            return DB::transaction(function () use ($data) {
                $dto = CreateItemDTO::fromArray($data);
                return Item::create($dto->toArray());
            });
        } catch (\Exception $e) {
            Log::error('Error creating item: ' . $e->getMessage(), [
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing item.
     *
     * @param int $id
     * @param array $data
     * @return Item
     * @throws \Illuminate\Validation\ValidationException
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function updateItem(int $id, array $data): Item
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $dto = UpdateItemDTO::fromArray($data);
                $item = Item::findOrFail($id);
                $item->update($dto->toArray());
                return $item->fresh();
            });
        } catch (ModelNotFoundException $e) {
            Log::error('Item not found for update: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating item: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Delete an item.
     *
     * @param int $id
     * @return bool
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
            Log::error('Item not found for deletion: ' . $id);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error deleting item: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Get a single item by ID.
     *
     * @param int $id
     * @return Item
     * @throws ModelNotFoundException
     */
    public function getItem(int $id): Item
    {
        try {
            return Item::with(['supplier', 'defaultBrand', 'group', 'orderingUnit', 'countingUnit'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error('Item not found: ' . $id);
            throw $e;
        }
    }
}
