<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Order\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OrderService
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    public function list(User $actor, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orders->paginateForUser($actor, $perPage);
    }

    public function show(User $actor, int $id): Order
    {
        return $this->orders->findForUserOrFail($actor, $id);
    }

    /**
     * Create an order with items in a transaction.
     * @param array{status?:string, items: array<array{product_id:int, quantity:int}>} $data
     */
    public function create(User $actor, array $data): Order
    {
        $items = collect($data['items'] ?? []);
        if ($items->isEmpty()) {
            throw new \InvalidArgumentException('Items are required');
        }

        // Load products once, keyed by id
        $productIds = $items->pluck('product_id')->all();
        $products = Product::query()
            ->select(['id','price'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return DB::transaction(function () use ($actor, $data, $items, $products) {
            $order = new Order();
            $order->user_id = $actor->id;
            $order->status = $data['status'] ?? 'pending';
            $order->total_amount = 0;
            $order->save();

            $total = 0;
            $now = now();
            $rows = [];

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = max(1, (int) $item['quantity']);
                $product = $products->get($productId);
                if (!$product) {
                    throw new \InvalidArgumentException("Invalid product_id: {$productId}");
                }
                $price = $product->price;
                $subtotal = bcmul((string)$price, (string)$qty, 2);
                $total = bcadd((string)$total, (string)$subtotal, 2);

                $rows[] = [
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk insert for performance
            OrderItem::insert($rows);

            $order->total_amount = $total;
            $order->save();

            return $order->fresh(['items.product']);
        });
    }

    /** Update order status only. */
    public function updateStatus(User $actor, int $id, string $status): Order
    {
        $order = $this->orders->findForUserOrFail($actor, $id);
        $order->status = $status;
        $order->save();
        return $order->fresh(['items.product']);
    }

    public function delete(User $actor, int $id): void
    {
        $order = $this->orders->findForUserOrFail($actor, $id);
        $order->delete();
    }
}
