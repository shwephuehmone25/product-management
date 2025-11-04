<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Resources\Product\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products)
    {
        $this->middleware('is.admin')->only(['store', 'update']);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $filters = [
            'q' => $request->query('q'),
            'name' => $request->query('name'),
            'sku' => $request->query('sku'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'min_stock' => $request->query('min_stock'),
            'max_stock' => $request->query('max_stock'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $request->query('sort'),
            'direction' => $request->query('direction'),
            'with_trashed' => $request->query('with_trashed'),
            'only_trashed' => $request->query('only_trashed'),
        ];

        $paginator = $this->products->list($request->user(), $perPage, $filters);

        return response()->json([
            'data' => ProductResource::collection($paginator->getCollection()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['data' => new ProductResource($product)]);
    }

    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        $product = $this->products->update($product, $request->validated());
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->delete($product);
        return response()->json(null, 204);
    }
}
