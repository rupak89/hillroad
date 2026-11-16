<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Services\ItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_item_changes_only_the_supplied_fields(): void
    {
        $item = Item::factory()->create([
            'item_name' => 'item-original',
            'latest_price' => 5,
        ]);

        $updated = app(ItemService::class)->updateItem($item->id, [
            'latest_price' => 12.5,
        ]);

        $this->assertSame('item-original', $updated->item_name);
        $this->assertEqualsWithDelta(12.5, (float) $updated->latest_price, 0.001);
    }

    public function test_unvalidated_http_requests_are_rejected_by_the_form_request(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/items', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['item_name', 'ordering_unit_id', 'counting_unit_id', 'latest_price']);

        $this->postJson('/api/recipes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['recipe_name']);
    }
}
