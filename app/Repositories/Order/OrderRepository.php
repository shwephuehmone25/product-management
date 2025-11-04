<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['items' => function ($q) {
                $q->select(['id','order_id','product_id','quantity','price','subtotal','created_at','updated_at'])
                  ->with(['product' => function ($p) {
                      $p->select(['id','name','sku','price']);
                  }]);
            }])
            ->select(['id','user_id','status','total_amount','created_at','updated_at'])
            ->latest('id');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->paginate($perPage);
    }

    public function findForUserOrFail(User $user, int $id): Order
    {
        $query = Order::query()
            ->with(['items' => function ($q) {
                $q->select(['id','order_id','product_id','quantity','price','subtotal','created_at','updated_at'])
                  ->with(['product' => function ($p) {
                      $p->select(['id','name','sku','price']);
                  }]);
            }])
            ->select(['id','user_id','status','total_amount','created_at','updated_at'])
            ->where('id', $id);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return $query->firstOrFail();
    }
}
