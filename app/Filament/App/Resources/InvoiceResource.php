<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Resolution;
use App\Services\Apidian\ApidianService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Facturas Electrónicas';
    
    protected static ?string $modelLabel = 'Factura Electrónica';
    
    protected static ?string $pluralModelLabel = 'Facturas Electrónicas';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la Factura')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre / Razón Social')
                                            ->required(),
                                        Forms\Components\TextInput::make('document_number')
                                            ->label('NIT / CC')
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->columnSpan(2),
                                    
                                Forms\Components\Select::make('resolution_id')
                                    ->label('Resolución DIAN')
                                    ->relationship('resolution', 'resolution_number')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $resolution = Resolution::find($state);
                                            $set('prefix', $resolution?->prefix);
                                            $set('number', $resolution?->next_number ?? 1);
                                        }
                                    }),
                            ]),
                            
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('prefix')
                                    ->label('Prefijo')
                                    ->maxLength(10)
                                    ->disabled()
                                    ->dehydrated(),
                                    
                                Forms\Components\TextInput::make('number')
                                    ->label('Número')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                    
                                Forms\Components\DatePicker::make('date')
                                    ->label('Fecha de Emisión')
                                    ->default(now())
                                    ->required(),
                                    
                                Forms\Components\TimePicker::make('time')
                                    ->label('Hora de Emisión')
                                    ->default(now())
                                    ->seconds(true)
                                    ->required(),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Forma de Pago')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('payment_form_id')
                                    ->label('Forma de Pago')
                                    ->options([
                                        1 => 'Contado',
                                        2 => 'Crédito',
                                    ])
                                    ->default(1)
                                    ->required()
                                    ->reactive(),
                                    
                                Forms\Components\Select::make('payment_method_id')
                                    ->label('Medio de Pago')
                                    ->options([
                                        10 => 'Efectivo',
                                        20 => 'Tarjeta débito',
                                        30 => 'Transferencia bancaria',
                                        31 => 'Consignación bancaria',
                                        42 => 'Tarjeta crédito',
                                        47 => 'Transferencia débito bancaria',
                                        48 => 'Tarjeta crédito',
                                        49 => 'Tarjeta débito',
                                    ])
                                    ->default(10)
                                    ->required(),
                                    
                                Forms\Components\TextInput::make('duration_measure')
                                    ->label('Días de Plazo')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $date = $get('date') ?? now();
                                        $set('payment_due_date', now()->parse($date)->addDays($state)->format('Y-m-d'));
                                    })
                                    ->visible(fn (Get $get) => $get('payment_form_id') == 2),
                                    
                                Forms\Components\DatePicker::make('payment_due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->visible(fn (Get $get) => $get('payment_form_id') == 2),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Productos y Servicios')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Producto')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            $set('code', $product->code);
                                            $set('description', $product->name);
                                            $set('price_amount', $product->price);
                                            $set('tax_percent', $product->tax_rate ?? '19.00');
                                            $set('unit_measure_id', 70);
                                        }
                                    })
                                    ->columnSpan(3),
                                    
                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(2),
                                    
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpan(3),
                                    
                                Forms\Components\TextInput::make('invoiced_quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                Forms\Components\TextInput::make('price_amount')
                                    ->label('Precio Unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                Forms\Components\TextInput::make('discount_percent')
                                    ->label('% Desc.')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                Forms\Components\Select::make('tax_percent')
                                    ->label('IVA')
                                    ->options([
                                        '0' => '0%',
                                        '5' => '5%',
                                        '19' => '19%',
                                    ])
                                    ->default('19')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
                                    
                                Forms\Components\TextInput::make('line_extension_amount')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('IVA')
                                    ->numeric()
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                    
                                // Campos ocultos pero necesarios para APIDIAN
                                Forms\Components\Hidden::make('unit_measure_id')->default(70),
                                Forms\Components\Hidden::make('base_quantity')->default(1),
                                Forms\Components\Hidden::make('taxable_amount'),
                                Forms\Components\Hidden::make('discount_amount'),
                                Forms\Components\Hidden::make('tax_id')->default(1),
                                Forms\Components\Hidden::make('type_item_identification_id')->default(4),
                                Forms\Components\Hidden::make('free_of_charge_indicator')->default(false),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Producto')
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->columnSpanFull(),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('totals')
                                    ->label('')
                                    ->content(new HtmlString('<div class="text-right"><strong>Resumen de Totales</strong></div>'))
                                    ->columnSpan(2),
                                    
                                Forms\Components\Placeholder::make('subtotal_display')
                                    ->label('Subtotal')
                                    ->content(function (Get $get) {
                                        $total = collect($get('items'))->sum('line_extension_amount');
                                        return '$' . number_format($total, 2);
                                    }),
                                    
                                Forms\Components\Placeholder::make('tax_display')
                                    ->label('IVA')
                                    ->content(function (Get $get) {
                                        $total = collect($get('items'))->sum('tax_amount');
                                        return '$' . number_format($total, 2);
                                    })
                                    ->columnStart(3),
                                    
                                Forms\Components\Placeholder::make('total_display')
                                    ->label('Total a Pagar')
                                    ->content(function (Get $get) {
                                        $subtotal = collect($get('items'))->sum('line_extension_amount');
                                        $tax = collect($get('items'))->sum('tax_amount');
                                        return new HtmlString('<strong style="font-size: 1.2em;">$' . number_format($subtotal + $tax, 2) . '</strong>');
                                    })
                                    ->columnStart(3),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas de la Factura')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Checkbox::make('sendmail')
                                    ->label('Enviar email al cliente'),
                                    
                                Forms\Components\Checkbox::make('sendmailtome')
                                    ->label('Enviar copia a mi correo'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    protected static function updateLineTotal(Set $set, Get $get): void
    {
        $quantity = floatval($get('invoiced_quantity') ?? 0);
        $price = floatval($get('price_amount') ?? 0);
        $discountPercent = floatval($get('discount_percent') ?? 0);
        $taxPercent = floatval($get('tax_percent') ?? 19);
        
        // Subtotal = Cantidad × Precio
        $subtotal = $quantity * $price;
        
        // Descuento
        $discountAmount = $subtotal * ($discountPercent / 100);
        
        // Base gravable
        $taxableAmount = $subtotal - $discountAmount;
        
        // IVA
        $taxAmount = $taxableAmount * ($taxPercent / 100);
        
        $set('line_extension_amount', round($subtotal, 2));
        $set('discount_amount', round($discountAmount, 2));
        $set('taxable_amount', round($taxableAmount, 2));
        $set('tax_amount', round($taxAmount, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_number')
                    ->label('Número')
                    ->searchable(['prefix', 'number'])
                    ->sortable()
                    ->weight('bold'),
                    
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
                        'gray' => 'voided',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'sent' => 'Enviada',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        'voided' => 'Anulada',
                        default => $state,
                    }),
                    
                Tables\Columns\IconColumn::make('cufe')
                    ->label('DIAN')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'sent' => 'Enviada',
                        'approved' => 'Aprobada',
                        'rejected' => 'Rechazada',
                        'voided' => 'Anulada',
                    ]),
                    
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Invoice $record) => $record->status === 'draft'),
                    
                    Tables\Actions\Action::make('send_to_dian')
                        ->label('Enviar a DIAN')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->visible(fn (Invoice $record) => $record->status === 'draft')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            try {
                                $apidian = app(ApidianService::class);
                                
                                // Calcular totales
                                $record->load('items');
                                $record->calculateTotals();
                                $record->save();
                                
                                // Enviar a APIDIAN
                                $response = $apidian->sendInvoice($record->toApidianFormat());
                                
                                // Actualizar registro
                                $record->update([
                                    'status' => 'sent',
                                    'cufe' => $response['cufe'] ?? null,
                                    'qr_code' => $response['qr_code'] ?? null,
                                    'zip_key' => $response['zip_key'] ?? null,
                                    'dian_status' => $response['status'] ?? 'sent',
                                    'dian_response' => $response,
                                    'sent_to_dian_at' => now(),
                                    'pdf_url' => $response['pdf_url'] ?? null,
                                    'xml_url' => $response['xml_url'] ?? null,
                                ]);
                                
                                Notification::make()
                                    ->success()
                                    ->title('Factura enviada a DIAN')
                                    ->body('La factura se ha enviado exitosamente.')
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->danger()
                                    ->title('Error al enviar factura')
                                    ->body($e->getMessage())
                                    ->send();
                            }
                        }),
                        
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Descargar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('primary')
                        ->visible(fn (Invoice $record) => !empty($record->pdf_url))
                        ->url(fn (Invoice $record) => $record->pdf_url, shouldOpenInNewTab: true),
                        
                    Tables\Actions\Action::make('download_xml')
                        ->label('Descargar XML')
                        ->icon('heroicon-o-code-bracket')
                        ->color('gray')
                        ->visible(fn (Invoice $record) => !empty($record->xml_url))
                        ->url(fn (Invoice $record) => $record->xml_url, shouldOpenInNewTab: true),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('delete_invoices')),
                ]),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
