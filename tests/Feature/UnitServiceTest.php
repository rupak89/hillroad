<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\UnitType;
use App\Services\UnitService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_unit_updates_the_existing_record_instead_of_creating_one(): void
    {
        $unit = Unit::factory()->create([
            'name' => 'unit-original',
            'ratio' => 1,
        ]);

        $updated = app(UnitService::class)->updateUnit($unit->id, [
            'name' => 'unit-renamed',
            'ratio' => 2.5,
        ]);

        $this->assertSame($unit->id, $updated->id);
        $this->assertSame('unit-renamed', $updated->name);
        $this->assertEqualsWithDelta(2.5, $updated->ratio, 0.001);
        $this->assertDatabaseCount('units', 1);
    }

    public function test_update_unit_throws_when_the_unit_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        app(UnitService::class)->updateUnit(999999, [
            'name' => 'unit-missing',
            'ratio' => 1,
        ]);
    }

    public function test_find_unit_by_id_returns_the_unit_or_null(): void
    {
        $unit = Unit::factory()->create();
        $service = app(UnitService::class);

        $this->assertTrue($unit->is($service->findUnitById((string) $unit->id)));
        $this->assertNull($service->findUnitById('999999'));
    }

    public function test_unit_type_factory_produces_a_valid_physical_type(): void
    {
        $unitType = UnitType::factory()->create();

        $this->assertTrue(class_exists($unitType->physical_type));
    }
}
