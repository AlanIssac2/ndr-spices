<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\IndiaPostTracker;

class CheckIndiaPostOrders extends Command
{
    protected $signature = 'orders:check-india-post';
    protected $description = 'Automatically check India Post tracking status and complete delivered orders';

    public function handle()
    {
        $dispatchedOrders = Order::where('status', 'dispatched')
            ->whereNotNull('tracking_number')
            ->get();

        if ($dispatchedOrders->isEmpty()) {
            $this->info("No dispatched orders with tracking numbers found.");
            return;
        }

        foreach ($dispatchedOrders as $order) {
            $tracking = IndiaPostTracker::checkStatus($order->tracking_number);

            if ($tracking && isset($tracking['status']) && str_contains(strtolower($tracking['status']), 'delivered')) {
                $order->update(['status' => 'completed']);
                $this->info("Order #{$order->id} automatically marked as Completed.");
            } else {
                $this->info("Order #{$order->id} is still in transit.");
            }
        }
    }
}