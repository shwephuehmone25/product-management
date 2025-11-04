<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Mail\OrderConfirmedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderConfirmed $event): void
    {
        $order = $event->order->loadMissing('user', 'items.product');

        Log::info('Order confirmed', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'total_amount' => (string) $order->total_amount,
        ]);

        if ($order->user && $order->user->email) {
            Mail::to($order->user->email)->queue(new OrderConfirmedMail($order));
        }
    }
}
