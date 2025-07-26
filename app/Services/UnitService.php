<?php

namespace App\Services;

use App\Models\Unit;
use Ramsey\Uuid\Type\Integer;

class UnitService
{

    /**
     * Get the list of units.
     *
     * @return array
     */
    public function getUnits()
    {
        return Unit::all();
    }

    /**
     * Create a new unit.
     *
     * @param array {name: string, ratio: float, unit_type_id: int} $data
     * @return Unit
     */
    public function createUnit(array $data) :Unit
    {
        return Unit::create($data);
    }

    /**
     * Update an existing unit.
     *
     * @param int $id
     * @param array $data
     * @return Unit
     */
    public function updateUnit(int $id, array $data): Unit
    {
        return Unit::updateOrCreate(['id' => $id], $data);
    }
    /**
     * Delete a unit.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteUnit(string $id): ?bool
    {
        $unit = Unit::find($id);
        if ($unit) {
            return $unit->delete();
        }
        return null;
    }
    /**
     * Find a unit by ID.
     *
     * @param int $id
     * @return Unit|null
     */
    public function findUnitById(string $id): ?Unit
    {
        return Unit::find($id)->first();
    }

}
