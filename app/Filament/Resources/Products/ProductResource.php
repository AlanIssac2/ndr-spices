<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

   public static function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
{
    return $form
        ->schema([
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
                
            
                
            \Filament\Forms\Components\Textarea::make('description')
                ->columnSpanFull(),
                
            \Filament\Forms\Components\FileUpload::make('media_paths')
                    ->label('Product Images & Videos')
                    ->disk('public') // Tells Laravel to put these in the public-facing folder
                    ->multiple()
                    ->reorderable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'video/mp4'])
                    ->directory('products')
                    ->maxFiles(5)
                    ->columnSpanFull(),
                
            \Filament\Forms\Components\Toggle::make('is_active')
                ->label('Is Active (Visible on Store)')
                ->default(true),
                
            \Filament\Forms\Components\Repeater::make('variants')
                ->relationship('variants')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('weight_label')
                        ->required()
                        ->placeholder('e.g., 100g'),
                    \Filament\Forms\Components\TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->prefix('₹'),
                    \Filament\Forms\Components\TextInput::make('stock')
                        ->numeric()
                        ->default(50),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ]);
}
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Bypassing the missing EditAction class. 
                // You can simply click anywhere on the product row to edit it!
            ])
            ->bulkActions([
                // Bypassing the missing DeleteBulkAction class.
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    protected static function mutateFormDataBeforeCreate(array $data): array
{
    if (empty($data['slug']) && !empty($data['name'])) {
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
    }
    
    return $data;
}

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
