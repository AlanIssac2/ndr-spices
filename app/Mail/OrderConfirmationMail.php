<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        // Eager load items and products to prevent null pointer errors in the email view
        $this->order = $order->load('items.product');
    }

    public function build()
    {
        return $this->subject('Order Confirmation - NDR Spices #' . $this->order->id)
                    ->view('emails.order-confirmation');
    }
}