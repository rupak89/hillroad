<?php

namespace App\Services;

use App\Models\Item;
use App\Http\Resources\ItemResource;

class ItemService
{
    /**
     * Get the list of items.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getItems()
    {
        return ItemResource::collection(Item::all());
    }

    /**
     * Create a new item.
     *
     * @param array $data { name: string, orderring_unit_id: int, counting_unit_id: int,
     * defaul_supplier_id: ?int, defalut_brand_id: ?int, group_id: ?int, latest_price: float }
     * @return Item
     */
    public function createItem(array $data): Item
    {
        return Item::create($data);
    }

    /**
     * Update an existing item.
     *
     * @param int $id
     * @param array $data
     * @return Item
     */
    public function updateItem(int $id, array $data): Item
    {
        return Item::updateOrCreate(['id' => $id], $data);
    }

    /**
     * Delete an item.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteItem(string $id): ?bool
    {
        $item = Item::find($id);
        if ($item) {
            return $item->delete();
        }
        return null;
    }
}
