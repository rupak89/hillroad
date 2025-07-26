<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'ordering_unit_id' => $this->ordering_unit_id,
            'ordering_unit' => '',
            'counting_unit_id' => $this->counting_unit_id,
            'default_supplier_id' => $this->default_supplier_id,
            'default_supplier' => 'Bidfood',
            'default_brand_id' => $this->default_brand_id,
            'group_id' => $this->group_id,
            'latest_price' => $this->latest_price,
        ];
    }
}
