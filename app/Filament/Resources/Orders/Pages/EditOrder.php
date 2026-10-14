<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_single')
                ->label('Print A4 Sheet')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->url(fn (): string => route('admin.orders.print', ['ids' => $this->record->id]))
                ->openUrlInNewTab(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}