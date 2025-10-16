<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProductResource\Pages;
use App\Filament\App\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationLabel = 'Productos';
    
    protected static ?string $modelLabel = 'Producto';
    
    protected static ?string $pluralModelLabel = 'Productos';
    
    protected static ?string $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Producto')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Código/SKU')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\TextInput::make('barcode')
                            ->label('Código de Barras')
                            ->maxLength(50),
                            
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Categoría')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->maxLength(65535),
                            ]),
                            
                        Forms\Components\Select::make('unit_measure_id')
                            ->label('Unidad de Medida')
                            ->options([
                                '94' => 'Unidad',
                                'KGM' => 'Kilogramo',
                                'GRM' => 'Gramo',
                                'LTR' => 'Litro',
                                'MTR' => 'Metro',
                                'CMT' => 'Centímetro',
                                'MTK' => 'Metro cuadrado',
                                'LBR' => 'Libra',
                                'ONZ' => 'Onza',
                                'GLI' => 'Galón',
                                'CEN' => 'Ciento',
                                'MIL' => 'Millar',
                            ])
                            ->required()
                            ->default('94')
                            ->searchable(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Precios e Impuestos')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Precio de Venta')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                            
                        Forms\Components\TextInput::make('cost')
                            ->label('Costo')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                            
                        Forms\Components\Select::make('tax_id')
                            ->label('Tipo de Impuesto (IVA)')
                            ->options([
                                '01' => 'IVA 19%',
                                '02' => 'IVA 5%',
                                '03' => 'IVA 0%',
                                '04' => 'IVA Excluido',
                                '05' => 'IVA Exento',
                            ])
                            ->required()
                            ->default('01')
                            ->searchable(),
                            
                        Forms\Components\TextInput::make('tax_percentage')
                            ->label('Porcentaje de IVA')
                            ->numeric()
                            ->suffix('%')
                            ->default(19)
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Inventario')
                    ->schema([
                        Forms\Components\Toggle::make('inventory_control')
                            ->label('Controlar Inventario')
                            ->default(true)
                            ->live(),
                            
                        Forms\Components\TextInput::make('stock')
                            ->label('Stock Actual')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('inventory_control')),
                            
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Stock Mínimo')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('inventory_control'))
                            ->helperText('Se generará alerta cuando el stock sea menor a este valor'),
                            
                        Forms\Components\TextInput::make('max_stock')
                            ->label('Stock Máximo')
                            ->numeric()
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('inventory_control'))
                            ->helperText('Stock máximo recomendado'),
                            
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Imagen')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Imagen del Producto')
                            ->image()
                            ->imageEditor()
                            ->directory('products')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Imagen')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-product.png')),
                    
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        !$record->inventory_control => 'gray',
                        $record->stock <= 0 => 'danger',
                        $record->stock <= $record->min_stock => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($record) => $record->inventory_control ? $record->stock : 'N/A'),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
                    
                Tables\Filters\TernaryFilter::make('inventory_control')
                    ->label('Control de Inventario')
                    ->placeholder('Todos')
                    ->trueLabel('Con control')
                    ->falseLabel('Sin control'),
                    
                Tables\Filters\Filter::make('bajo_stock')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<=', 'min_stock')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear Producto'),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        $lowStock = static::getModel()::where('inventory_control', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();
            
        return $lowStock > 0 ? (string) $lowStock : null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
