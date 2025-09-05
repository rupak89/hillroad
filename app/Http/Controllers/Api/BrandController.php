<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Http\Requests\BrandRequest;
use Illuminate\Http\Request;

class BrandController extends Controller
{

    function __construct(private BrandService $brandService)
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $brands = $this->brandService->getBrands($perPage);

        return response()->json([
            'message' => 'Brands list',
            'brands' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        $brand = $this->brandService->createBrand($request->validated());

        return response()->json([
            'message' => 'Brand created successfully',
            'brand' => $brand,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = $this->brandService->getBrand($id);

        if (!$brand) {
            return response()->json([
                'message' => 'Brand not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Brand details',
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, string $id)
    {
        $brand = $this->brandService->updateBrand($id, $request->validated());

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand' => $brand,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->brandService->deleteBrand($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Brand deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Brand not found or could not be deleted',
        ], 404);
    }
}
