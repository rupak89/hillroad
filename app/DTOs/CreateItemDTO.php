<?php

namespace App\DTOs;

class CreateItemDTO extends BaseDTO
{
    public string $item_name;
    public int $ordering_unit_id;
    public int $counting_unit_id;
    public ?int $default_supplier_id = null;
    public ?int $default_brand_id = null;
    public ?int $group_id = null;
    public ?float $latest_price = null;

    protected function rules(): array
    {
        return [
            'item_name' => 'required|string|max:255',
            'ordering_unit_id' => 'required|integer|exists:units,id',
            'counting_unit_id' => 'required|integer|exists:units,id',
            'default_supplier_id' => 'nullable|integer|exists:suppliers,id',
            'default_brand_id' => 'nullable|integer|exists:brands,id',
            'group_id' => 'nullable|integer|exists:groups,id',
            'latest_price' => 'nullable|decimal:0,2|min:0',
        ];
    }

    protected function fill(array $data): void
    {
        $this->item_name = $data['item_name'];
        $this->ordering_unit_id = $data['ordering_unit_id'];
        $this->counting_unit_id = $data['counting_unit_id'];
        $this->default_supplier_id = $data['default_supplier_id'] ?? null;
        $this->default_brand_id = $data['default_brand_id'] ?? null;
        $this->group_id = $data['group_id'] ?? null;
        $this->latest_price = $data['latest_price'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'item_name' => $this->item_name,
            'ordering_unit_id' => $this->ordering_unit_id,
            'counting_unit_id' => $this->counting_unit_id,
            'default_supplier_id' => $this->default_supplier_id,
            'default_brand_id' => $this->default_brand_id,
            'group_id' => $this->group_id,
            'latest_price' => $this->latest_price,
        ];
    }
}
