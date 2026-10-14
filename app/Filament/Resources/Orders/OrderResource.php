<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Customer Name')
                    ->required(),
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->required(),
                Textarea::make('address')
                    ->label('Shipping Address')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('subtotal')
                    ->numeric()
                    ->prefix('₹')
                    ->readOnly(),
                TextInput::make('shipping')
                    ->label('Shipping Fee')
                    ->numeric()
                    ->prefix('₹')
                    ->readOnly(),
                TextInput::make('total')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('₹')
                    ->readOnly(),

                Select::make('payment_method')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'online' => 'Online Payment',
                    ])
                    ->disabled(),
                
                // Entering India Post tracking automatically switches status to 'dispatched'
                TextInput::make('tracking_number')
                    ->label('India Post Tracking Number')
                    ->placeholder('e.g., CP123456789IN')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!empty($state)) {
                            $set('status', 'dispatched');
                        }
                    }),
                
                Select::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'dispatched' => 'Dispatched',
                        'completed' => 'Completed',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->label('Phone'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('inr')
                    ->sortable(),
                TextColumn::make('tracking_number')
                    ->label('India Post Tracking')
                    ->searchable()
                    ->placeholder('No tracking yet'),
                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'dispatched' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}