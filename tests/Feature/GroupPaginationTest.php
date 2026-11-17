<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_index_rejects_invalid_per_page(): void
    {
        Sanctum::actingAs(User::factory()->create());

        foreach ([0, 101, -1] as $perPage) {
            $this->getJson("/api/groups?per_page={$perPage}")
                ->assertUnprocessable()
                ->assertJsonValidationErrors('per_page');
        }
    }

    public function test_group_items_reject_invalid_per_page(): void
    {
        $group = Group::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/groups/{$group->id}/items?per_page=0")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_group_items_returns_404_for_a_missing_group(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/groups/999999/items')->assertNotFound();
    }

    public function test_group_items_accepts_a_valid_request(): void
    {
        $group = Group::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/groups/{$group->id}/items?per_page=25&search=flour")
            ->assertOk()
            ->assertJsonPath('group.id', $group->id)
            ->assertJsonPath('items.per_page', 25);
    }

    public function test_group_items_exposes_ordering_and_counting_units(): void
    {
        $group = Group::factory()->create();
        $item = Item::factory()->create(['group_id' => $group->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/groups/{$group->id}/items")
            ->assertOk()
            ->assertJsonPath('items.data.0.ordering_unit.name', $item->orderingUnit->name)
            ->assertJsonPath('items.data.0.counting_unit.name', $item->countingUnit->name)
            ->assertJsonPath('items.data.0.supplier.supplier_name', $item->supplier->supplier_name);
    }
}
