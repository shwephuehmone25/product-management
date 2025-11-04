<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->select(['id','name','sku','price','stock','created_at','updated_at'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()
            ->select(['id','name','sku','price','stock','created_at','updated_at'])
            ->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
