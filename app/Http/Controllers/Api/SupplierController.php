<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use App\Http\Requests\SupplierRequest;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    function __construct(private SupplierService $supplierService)
    {

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $suppliers = $this->supplierService->getSuppliers($perPage);

        return response()->json([
            'message' => 'Suppliers list',
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $supplier = $this->supplierService->createSupplier($request->validated());

        return response()->json([
            'message' => 'Supplier created successfully',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = $this->supplierService->getSupplier($id);

        if (!$supplier) {
            return response()->json([
                'message' => 'Supplier not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Supplier details',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, string $id)
    {
        $supplier = $this->supplierService->updateSupplier($id, $request->validated());

        return response()->json([
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->supplierService->deleteSupplier($id);

        if ($deleted) {
            return response()->json([
                'message' => 'Supplier deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Supplier not found or could not be deleted',
        ], 404);
    }
}
