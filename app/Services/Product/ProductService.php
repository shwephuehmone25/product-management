<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;

class ProductService
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function list(User $actor, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $withTrashed = false;
        $onlyTrashed = false;

        if (($actor->role ?? null) === 'admin') {
            $withTrashed = filter_var($filters['with_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $onlyTrashed = filter_var($filters['only_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->products->paginate($filters, $perPage, $withTrashed, $onlyTrashed);
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
