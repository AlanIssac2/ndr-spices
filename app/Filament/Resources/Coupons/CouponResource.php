<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Coupon Details')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., NDR50')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                            
                        \Filament\Forms\Components\Select::make('discount_type')
                            ->options([
                                'flat' => 'Flat Amount (₹)',
                                'percent' => 'Percentage (%)',
                            ])
                            ->required(),
                            
                        \Filament\Forms\Components\TextInput::make('discount_value')
                            ->required()
                            ->numeric(),
                            
                        \Filament\Forms\Components\TextInput::make('min_order_value')
                            ->numeric()
                            ->prefix('₹')
                            ->placeholder('e.g., 500 (Optional)'),
                            
                        \Filament\Forms\Components\DatePicker::make('expires_at')
                            ->label('Expiration Date'),
                            
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2)
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('discount_type')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('discount_value'),
                \Filament\Tables\Columns\TextColumn::make('min_order_value')
                    ->money('inr'),
                \Filament\Tables\Columns\TextColumn::make('expires_at')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
