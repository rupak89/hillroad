<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Group;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            // items.item_name is unique.
            'item_name' => 'item-'.Str::lower(Str::random(10)),
            'ordering_unit_id' => Unit::factory(),
            'counting_unit_id' => Unit::factory(),
            'default_supplier_id' => Supplier::factory(),
            'default_brand_id' => Brand::factory(),
            'group_id' => Group::factory(),
            'latest_price' => $this->faker->randomFloat(2, 1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
