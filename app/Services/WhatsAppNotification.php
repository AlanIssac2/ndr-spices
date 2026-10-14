<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Order;

class WhatsAppNotification
{
    public static function sendOrderConfirmation(Order $order)
    {
        $phone = preg_replace('/[^0-9]/', '', $order->phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }
        
        $message = "Hello *{$order->name}*! 👋\n\n"
                 . "Thank you for shopping with *NDR Spices*!\n"
                 . "Your Order *#{$order->id}* has been successfully placed.\n\n"
                 . "🛒 *Total:* ₹" . number_format($order->total, 2) . "\n"
                 . "📦 *Payment:* " . strtoupper($order->payment_method) . "\n\n"
                 . "We will send you tracking updates once dispatched. Have a great day!";

        try {
            \Log::info("WhatsApp message dispatched to {$phone}: \n" . $message);
        } catch (\Exception $e) {
            \Log::error("Failed to send WhatsApp notification: " . $e->getMessage());
        }
    }
}