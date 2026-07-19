<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        $mail = $this->subject("New Order {$this->order->order_number} — {$this->order->customer_name}")
            ->view('emails.orders.new-for-salon');

        if ($this->order->customer_email) {
            $mail->replyTo($this->order->customer_email, $this->order->customer_name);
        }

        return $mail;
    }
}
