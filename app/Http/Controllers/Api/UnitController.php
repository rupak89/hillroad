<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Services\UnitService;


class UnitController extends Controller
{

    function __construct(private UnitService $unitService)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'message' => 'List of units',
            'units' => $this->unitService->getUnits(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnitRequest $request)
    {

        $unit = $this->unitService->createUnit($request->validated());

        return response()->json([
            'message' => 'Unit created successfully',
            'unit' => $unit,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, string $id)
    {

        $unit = $this->unitService->updateUnit($id, $request->validated());


        return response()->json([
            'message' => 'Unit updated successfully',
            'unit' => $unit,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->unitService->deleteUnit($id);

        return response()->json([
            'message' => 'Unit deleted successfully',
        ]);
    }
}
