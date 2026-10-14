<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        return [
            Stat::make('Total Sales Revenue', '₹' . number_format($totalRevenue, 2))
                ->description('All time store earnings')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('success'),

            Stat::make('Completed Orders', $completedOrders)
                ->description('Successfully delivered')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting dispatch / printing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}