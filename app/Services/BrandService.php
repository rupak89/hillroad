<?php

namespace App\Services;

use App\Models\Brand;

class BrandService
{
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
