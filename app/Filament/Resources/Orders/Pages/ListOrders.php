<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $currentStatus = request()->query('status', 'pending');

        return [
            // Status Switcher Buttons
            Action::make('view_pending')
                ->label('Pending Orders')
                ->color($currentStatus === 'pending' ? 'warning' : 'gray')
                ->url(fn () => static::getResource()::getUrl('index', ['status' => 'pending'])),

            Action::make('view_dispatched')
                ->label('Dispatched Orders')
                ->color($currentStatus === 'dispatched' ? 'info' : 'gray')
                ->url(fn () => static::getResource()::getUrl('index', ['status' => 'dispatched'])),

            Action::make('view_completed')
                ->label('Completed Orders')
                ->color($currentStatus === 'completed' ? 'success' : 'gray')
                ->url(fn () => static::getResource()::getUrl('index', ['status' => 'completed'])),

            // Smart Print Button ONLY for New/Unprinted Pending Orders
            Action::make('print_new_pending')
                ->label('Print New Pending Sheets')
                ->icon('heroicon-m-printer')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Print Unprinted Pending Orders')
                ->modalDescription('This will print all brand-new pending orders that have never been printed before and lock them so they are not reprinted.')
                ->action(function () {
                    // Fetch only pending orders that have NOT been printed yet
                    $unprintedOrders = Order::where('status', 'pending')
                        ->whereNull('printed_at')
                        ->get();

                    if ($unprintedOrders->isEmpty()) {
                        Notification::make()
                            ->title('No new pending orders found to print.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $ids = $unprintedOrders->pluck('id')->implode(',');

                    // Mark these orders as printed right now
                    Order::whereIn('id', $unprintedOrders->pluck('id'))->update([
                        'printed_at' => now(),
                    ]);

                    // Open the print window
                    return redirect()->route('admin.orders.print', ['ids' => $ids]);
                }),

            // Manual Select Print Button
            Action::make('print_selected')
                ->label('Print Specific Orders')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->form([
                    Select::make('order_ids')
                        ->label('Select Orders to Print')
                        ->multiple()
                        ->searchable()
                        ->options(fn () => Order::all()->mapWithKeys(fn ($order) => [
                            $order->id => "Order #{$order->id} - {$order->name} (₹{$order->total}) [" . strtoupper($order->status) . "]"
                        ]))
                        ->required()
                ])
                ->action(function (array $data) {
                    $ids = implode(',', $data['order_ids']);
                    return redirect()->route('admin.orders.print', ['ids' => $ids]);
                }),
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();
        $status = request()->query('status', 'pending');

        return $query->where('status', $status);
    }
}