<?php

namespace App\Filament\App\Resources;

use App\Enums\DiscrepancyCode;
use App\Filament\App\Resources\DebitNoteResource\Pages;
use App\Models\DebitNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Resolution;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class DebitNoteResource extends Resource
{
    protected static ?string $model = DebitNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    
    protected static ?string $navigationLabel = 'Notas Débito';
    
    protected static ?string $modelLabel = 'Nota Débito';
    
    protected static ?string $pluralModelLabel = 'Notas Débito';
    
    protected static ?string $navigationGroup = 'Facturación Electrónica';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Sección 1: Referencia a Factura Original
                Section::make('Factura Original')
                    ->description('Seleccione la factura a la que se le aplicará la nota débito')
                    ->schema([
                        Select::make('invoice_id')
                            ->label('Factura')
                            ->relationship('invoice', 'id')
                            ->getOptionLabelFromRecordUsing(fn(Invoice $record) => "{$record->prefix}{$record->number} - {$record->customer->name} - $ " . number_format($record->payable_amount, 2))
                            ->searchable(['prefix', 'number'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $invoice = Invoice::with('customer', 'resolution')->find($state);
                                    if ($invoice) {
                                        $set('customer_id', $invoice->customer_id);
                                        $set('resolution_id', $invoice->resolution_id);
                                        $set('billing_reference_number', $invoice->prefix . $invoice->number);
                                        $set('billing_reference_uuid', $invoice->cufe);
                                        $set('billing_reference_issue_date', $invoice->date->format('Y-m-d'));
                                    }
                                }
                            })
                            ->columnSpan(2),
                            
                        TextInput::make('billing_reference_number')
                            ->label('Número Factura Original')
                            ->disabled()
                            ->dehydrated(),
                            
                        TextInput::make('billing_reference_uuid')
                            ->label('CUFE Factura Original')
                            ->disabled()
                            ->dehydrated(),
                            
                        TextInput::make('billing_reference_issue_date')
                            ->label('Fecha Emisión Factura')
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(2),

                // Sección 2: Información General
                Section::make('Información de la Nota Débito')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                            
                        Select::make('resolution_id')
                            ->label('Resolución DIAN')
                            ->options(Resolution::query()
                                ->where('type_document_id', 91) // Nota Débito
                                ->where('date_from', '<=', Carbon::today())
                                ->where('date_to', '>=', Carbon::today())
                                ->get()
                                ->mapWithKeys(fn($r) => [$r->id => "{$r->resolution_number} ({$r->prefix}{$r->from} - {$r->prefix}{$r->to})"]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $resolution = Resolution::find($state);
                                    $set('prefix', $resolution->prefix);
                                    $set('number', $resolution->getNextNumber());
                                }
                            }),
                            
                        TextInput::make('prefix')
                            ->label('Prefijo')
                            ->disabled()
                            ->dehydrated(),
                            
                        TextInput::make('number')
                            ->label('Número')
                            ->disabled()
                            ->dehydrated(),
                            
                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\TimePicker::make('time')
                            ->label('Hora')
                            ->default(now()->format('H:i:s'))
                            ->required(),
                    ])
                    ->columns(2),

                // Sección 3: Razón de la Nota (Discrepancy Response)
                Section::make('Motivo de la Nota Débito')
                    ->description('Indique el motivo de la emisión de esta nota débito')
                    ->schema([
                        Select::make('discrepancy_response_code')
                            ->label('Código de Motivo')
                            ->options(DiscrepancyCode::getDebitNoteCodes())
                            ->required()
                            ->reactive()
                            ->columnSpan(2),
                            
                        Textarea::make('discrepancy_response_description')
                            ->label('Descripción del Motivo')
                            ->required()
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                // Sección 4: Ítems de la Nota Débito
                Section::make('Ítems')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Producto')
                                    ->options(Product::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            $set('code', $product->code);
                                            $set('description', $product->name);
                                            $set('price_amount', $product->price);
                                            $set('tax_percent', $product->tax_rate);
                                        }
                                    })
                                    ->columnSpan(2),
                                    
                                TextInput::make('code')
                                    ->label('Código')
                                    ->required(),
                                    
                                TextInput::make('description')
                                    ->label('Descripción')
                                    ->required()
                                    ->columnSpan(2),
                                    
                                TextInput::make('invoiced_quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                TextInput::make('price_amount')
                                    ->label('Precio')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                TextInput::make('discount_percent')
                                    ->label('Descuento %')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                TextInput::make('tax_percent')
                                    ->label('IVA %')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(19)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                TextInput::make('line_extension_amount')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                                    
                                TextInput::make('tax_amount')
                                    ->label('IVA')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Ítem')
                            ->collapsible(),
                    ]),

                // Sección 5: Totales (calculados automáticamente)
                Section::make('Totales')
                    ->schema([
                        TextInput::make('line_extension_amount')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                            
                        TextInput::make('tax_inclusive_amount')
                            ->label('IVA')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                            
                        TextInput::make('payable_amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->extraAttributes(['class' => 'text-lg font-bold']),
                    ])
                    ->columns(3),

                // Sección 6: Configuración de Envío
                Section::make('Opciones de Envío')
                    ->schema([
                        Toggle::make('sendmail')
                            ->label('Enviar por email al cliente')
                            ->default(true),
                            
                        Toggle::make('sendmailtome')
                            ->label('Enviarme una copia'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_number')
                    ->label('Número')
                    ->searchable(['prefix', 'number'])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('invoice.full_number')
                    ->label('Factura Afectada')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payable_amount')
                    ->label('Total')
                    ->money('COP')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'sent',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Borrador',
                        'sent' => 'Enviada',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        default => $state,
                    }),
                    
                Tables\Columns\IconColumn::make('cude')
                    ->label('CUDE')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviada',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(DebitNote $record) => $record->is_draft),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function updateLineTotal(Set $set, Get $get): void
    {
        $quantity = (float) $get('invoiced_quantity') ?: 0;
        $price = (float) $get('price_amount') ?: 0;
        $discountPercent = (float) $get('discount_percent') ?: 0;
        $taxPercent = (float) $get('tax_percent') ?: 0;

        // Subtotal
        $subtotal = $quantity * $price;

        // Descuento
        $discount = $subtotal * ($discountPercent / 100);
        $subtotalWithDiscount = $subtotal - $discount;

        // IVA
        $tax = $subtotalWithDiscount * ($taxPercent / 100);

        $set('line_extension_amount', number_format($subtotalWithDiscount, 2, '.', ''));
        $set('taxable_amount', number_format($subtotalWithDiscount, 2, '.', ''));
        $set('tax_amount', number_format($tax, 2, '.', ''));
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
            'index' => Pages\ListDebitNotes::route('/'),
            'create' => Pages\CreateDebitNote::route('/create'),
            'view' => Pages\ViewDebitNote::route('/{record}'),
            'edit' => Pages\EditDebitNote::route('/{record}/edit'),
        ];
    }
}
