<?php

namespace App\DTOs;

class UpdateItemDTO extends BaseDTO
{
    public ?string $item_name = null;
    public ?int $ordering_unit_id = null;
    public ?int $counting_unit_id = null;
    public ?int $default_supplier_id = null;
    public ?int $default_brand_id = null;
    public ?int $group_id = null;
    public ?float $latest_price = null;

    protected function rules(): array
    {
        return [
            'item_name' => 'sometimes|string|max:255',
            'ordering_unit_id' => 'sometimes|integer|exists:units,id',
            'counting_unit_id' => 'sometimes|integer|exists:units,id',
            'default_supplier_id' => 'nullable|integer|exists:suppliers,id',
            'default_brand_id' => 'nullable|integer|exists:brands,id',
            'group_id' => 'nullable|integer|exists:groups,id',
            'latest_price' => 'nullable|decimal:0,2|min:0',
        ];
    }

    protected function fill(array $data): void
    {
        $this->item_name = $data['item_name'] ?? $this->item_name;
        $this->ordering_unit_id = $data['ordering_unit_id'] ?? $this->ordering_unit_id;
        $this->counting_unit_id = $data['counting_unit_id'] ?? $this->counting_unit_id;
        $this->default_supplier_id = array_key_exists('default_supplier_id', $data) ? $data['default_supplier_id'] : $this->default_supplier_id;
        $this->default_brand_id = array_key_exists('default_brand_id', $data) ? $data['default_brand_id'] : $this->default_brand_id;
        $this->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $this->group_id;
        $this->latest_price = array_key_exists('latest_price', $data) ? $data['latest_price'] : $this->latest_price;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->item_name !== null) {
            $data['item_name'] = $this->item_name;
        }
        if ($this->ordering_unit_id !== null) {
            $data['ordering_unit_id'] = $this->ordering_unit_id;
        }
        if ($this->counting_unit_id !== null) {
            $data['counting_unit_id'] = $this->counting_unit_id;
        }
        if ($this->default_supplier_id !== null) {
            $data['default_supplier_id'] = $this->default_supplier_id;
        }
        if ($this->default_brand_id !== null) {
            $data['default_brand_id'] = $this->default_brand_id;
        }
        if ($this->group_id !== null) {
            $data['group_id'] = $this->group_id;
        }
        if ($this->latest_price !== null) {
            $data['latest_price'] = $this->latest_price;
        }

        return $data;
    }
}
