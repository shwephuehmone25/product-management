<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Http\Requests\Order\OrderUpdateRequest;
use App\Http\Resources\Order\OrderResource;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $paginator = $this->orders->list($request->user(), $perPage);

        return response()->json([
            'data' => OrderResource::collection($paginator->getCollection()->load('items.product')),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(OrderStoreRequest $request): JsonResponse
    {
        $order = $this->orders->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order->load('items.product')),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orders->show($request->user(), $id);
        return response()->json(['data' => new OrderResource($order->load('items.product'))]);
    }

    public function update(OrderUpdateRequest $request, int $id): JsonResponse
    {
        $order = $this->orders->updateStatus($request->user(), $id, $request->validated()['status']);
        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order->load('items.product')),
        ]);
    }

    // Admin-only status update endpoint
    public function updateStatus(OrderUpdateRequest $request, int $id): JsonResponse
    {
        $order = $this->orders->updateStatus($request->user(), $id, $request->validated()['status']);
        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => new OrderResource($order->load('items.product')),
        ]);
    }
}
