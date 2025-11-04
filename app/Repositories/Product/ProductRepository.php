<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Paginate products with optional filters and soft-delete scopes.
     * Supported filters: q, name, sku, min_price, max_price, sort, direction.
     */
    public function paginate(array $filters = [], int $perPage = 15, bool $withTrashed = false, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = Product::query()
            ->select(['id','name','sku','price','stock','created_at','updated_at']);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        } elseif ($withTrashed) {
            $query->withTrashed();
        }

        if ($search = trim((string)($filters['q'] ?? ''))) {
            $like = $this->likeOperator();
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%$search%")
                  ->orWhere('sku', $like, "%$search%");
            });
        }

        if ($name = trim((string)($filters['name'] ?? ''))) {
            $query->where('name', $this->likeOperator(), "%$name%");
        }

        if ($sku = trim((string)($filters['sku'] ?? ''))) {
            // Case-insensitive equality
            $query->whereRaw('LOWER(sku) = ?', [strtolower($sku)]);
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['min_stock']) && is_numeric($filters['min_stock'])) {
            $query->where('stock', '>=', (int) $filters['min_stock']);
        }
        if (isset($filters['max_stock']) && is_numeric($filters['max_stock'])) {
            $query->where('stock', '<=', (int) $filters['max_stock']);
        }

        if (!empty($filters['date_from'])) {
            try {
                $from = Carbon::parse($filters['date_from'])->startOfDay();
                $query->where('created_at', '>=', $from);
            } catch (\Throwable $e) {
                // ignore invalid date
            }
        }
        if (!empty($filters['date_to'])) {
            try {
                $to = Carbon::parse($filters['date_to'])->endOfDay();
                $query->where('created_at', '<=', $to);
            } catch (\Throwable $e) {
                // ignore invalid date
            }
        }

        $allowedSorts = ['id','name','price','stock','created_at'];
        $sort = in_array($filters['sort'] ?? '', $allowedSorts, true) ? $filters['sort'] : 'id';
        $direction = strtolower((string)($filters['direction'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);

        return $query->paginate($perPage);
    }

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
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
