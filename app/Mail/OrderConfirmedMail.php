<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->onQueue('notifications');
    }

    public function build(): self
    {
        return $this->subject('Your order #'.$this->order->id.' is confirmed')
            ->markdown('mail.order.confirmed', [
                'order' => $this->order,
                'user' => $this->order->user,
            ]);
    }
}
