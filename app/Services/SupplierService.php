<?php

namespace App\Services;
use App\Models\Supplier;

class SupplierService
{
    /**
     * Get the list of suppliers with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSuppliers($perPage = 10)
    {
        return Supplier::orderBy('supplier_name')
            ->paginate($perPage);
    }

    /**
     * Create a new supplier.
     *
     * @param array $data { name: string, contact_info: string }
     * @return Supplier
     */
    public function createSupplier(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     *
     * @param int $id
     * @param array $data
     * @return Supplier
     */
    public function updateSupplier(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete a supplier.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteSupplier(string $id): ?bool
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            return $supplier->delete();
        }
        return null;
    }

    /**
     * Get a single supplier by ID.
     *
     * @param int $id
     * @return Supplier|null
     */
    public function getSupplier(int $id): ?Supplier
    {
        return Supplier::find($id);
    }
}
