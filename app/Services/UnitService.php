<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UnitService
{
    /**
     * Get the list of units.
     *
     * @return Collection<int, Unit>
     */
    public function getUnits(): Collection
    {
        return Unit::all();
    }

    /**
     * Create a new unit.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUnit(array $data): Unit
    {
        return Unit::create($data);
    }

    /**
     * Update an existing unit.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function updateUnit(int $id, array $data): Unit
    {
        $unit = Unit::findOrFail($id);
        $unit->update($data);

        return $unit->fresh();
    }

    /**
     * Delete a unit.
     *
     * @throws ModelNotFoundException
     */
    public function deleteUnit(string $id): bool
    {
        return Unit::findOrFail($id)->delete();
    }

    /**
     * Find a unit by ID.
     */
    public function findUnitById(string $id): ?Unit
    {
        return Unit::find($id);
    }
}
