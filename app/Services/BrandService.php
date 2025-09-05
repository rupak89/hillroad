<?php

namespace App\Services;

use App\Models\Brand;

class BrandService
{
    /**
     * Get the list of brands with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBrands($perPage = 10)
    {
        return Brand::withCount('items')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get all brands.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBrands()
    {
        return Brand::all();
    }

    /**
     * Create a new brand.
     *
     * @param array $data
     * @return \App\Models\Brand
     */
    public function createBrand(array $data)
    {
        return Brand::create($data);
    }

    /**
     * Get a single brand by ID.
     *
     * @param int $id
     * @return \App\Models\Brand|null
     */
    public function getBrand(int $id)
    {
        return Brand::find($id);
    }

    /**
     * Update an existing brand.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Brand
     */
    public function updateBrand(int $id, array $data)
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }

    public function deleteBrand(string $id): ?bool
    {
        $brand = Brand::find($id);
        if ($brand) {
            return $brand->delete();
        }
        return null;
    }
}
