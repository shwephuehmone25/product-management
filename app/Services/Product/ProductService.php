<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return $this->products->paginate($perPage);
    }

    public function get(int $id): Product
    {
        return $this->products->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return $this->products->create($data);
    }

    public function update(\App\Models\Product $product, array $data): Product
    {
        return $this->products->update($product, $data);
    }

    public function delete(\App\Models\Product $product): void
    {
        $this->products->delete($product);
    }
}
