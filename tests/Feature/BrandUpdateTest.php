<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BrandUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_brand_can_be_updated_without_changing_its_name(): void
    {
        $brand = Brand::create([
            'name' => 'Acme',
            'description' => 'Old description',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/brands/{$brand->id}", [
            'name' => 'Acme',
            'description' => 'New description',
        ])->assertOk()
            ->assertJsonPath('brand.name', 'Acme')
            ->assertJsonPath('brand.description', 'New description');

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Acme',
            'description' => 'New description',
        ]);
    }

    public function test_a_brand_can_be_renamed_to_a_unique_name(): void
    {
        $brand = Brand::create(['name' => 'Acme']);

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/brands/{$brand->id}", [
            'name' => 'Acme Renamed',
        ])->assertOk()
            ->assertJsonPath('brand.name', 'Acme Renamed');
    }

    public function test_a_brand_cannot_take_another_brands_name(): void
    {
        Brand::create(['name' => 'Acme']);
        $other = Brand::create(['name' => 'Other']);

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/brands/{$other->id}", [
            'name' => 'Acme',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_creating_a_duplicate_brand_is_still_rejected(): void
    {
        Brand::create(['name' => 'Acme']);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/brands', [
            'name' => 'Acme',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }
}
