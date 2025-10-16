<?php

namespace App\Filament\App\Resources;

use App\Enums\PayrollType;
use App\Filament\App\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\Resolution;
use App\Models\Worker;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationLabel = 'Nómina';
    
    protected static ?string $modelLabel = 'Nómina';
    
    protected static ?string $pluralModelLabel = 'Nóminas';
    
    protected static ?string $navigationGroup = 'Nómina Electrónica';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Sección 1: Trabajador y Periodo
                Section::make('Trabajador y Periodo')
                    ->description('Seleccione el trabajador y el periodo de nómina')
                    ->schema([
                        Select::make('worker_id')
                            ->label('Trabajador')
                            ->relationship('worker', 'identification_number')
                            ->getOptionLabelFromRecordUsing(fn(Worker $record) => "{$record->full_name} - {$record->identification_number}")
                            ->searchable(['first_name', 'surname', 'identification_number'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $worker = Worker::find($state);
                                    $set('salary', $worker->salary);
                                    $set('transport_allowance', $worker->calculateTransportAllowance());
                                }
                            })
                            ->columnSpan(2),
                            
                        Select::make('payroll_type_id')
                            ->label('Tipo de Nómina')
                            ->options(PayrollType::getTypes())
                            ->default('1')
                            ->required(),
                            
                        Select::make('payment_method_id')
                            ->label('Método de Pago')
                            ->options(PayrollType::getPaymentMethods())
                            ->default('42')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('period_start_date')
                            ->label('Inicio Periodo')
                            ->default(now()->startOfMonth())
                            ->required(),
                            
                        Forms\Components\DatePicker::make('period_end_date')
                            ->label('Fin Periodo')
                            ->default(now()->endOfMonth())
                            ->required(),
                            
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->default(now())
                            ->required(),
                            
                        TextInput::make('worked_days')
                            ->label('Días Trabajados')
                            ->numeric()
                            ->default(30)
                            ->required(),
                    ])
                    ->columns(2),

                // Sección 2: Numeración
                Section::make('Numeración')
                    ->schema([
                        Select::make('resolution_id')
                            ->label('Resolución DIAN')
                            ->options(Resolution::query()
                                ->where('type_document_id', 102) // Nómina
                                ->where('date_from', '<=', Carbon::today())
                                ->where('date_to', '>=', Carbon::today())
                                ->get()
                                ->mapWithKeys(fn($r) => [$r->id => "{$r->resolution_number} ({$r->prefix}{$r->from} - {$r->prefix}{$r->to})"]))
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $resolution = Resolution::find($state);
                                    $set('prefix', $resolution->prefix);
                                    $set('number', $resolution->getNextNumber());
                                    $set('consecutive', $resolution->getNextNumber());
                                }
                            }),
                            
                        TextInput::make('prefix')
                            ->label('Prefijo')
                            ->default('NOM')
                            ->required(),
                            
                        TextInput::make('number')
                            ->label('Número')
                            ->numeric()
                            ->required(),
                            
                        TextInput::make('consecutive')
                            ->label('Consecutivo')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(4),

                // Sección 3: Devengados
                Section::make('Devengados')
                    ->description('Conceptos que el trabajador devenga en este periodo')
                    ->schema([
                        TextInput::make('salary')
                            ->label('Salario Básico')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('transport_allowance')
                            ->label('Auxilio de Transporte')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('overtime')
                            ->label('Horas Extras')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('bonuses')
                            ->label('Bonificaciones')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('commissions')
                            ->label('Comisiones')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('severance')
                            ->label('Cesantías')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('vacation')
                            ->label('Vacaciones')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('other_accruals')
                            ->label('Otros Devengados')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('total_accruals')
                            ->label('Total Devengado')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold']),
                    ])
                    ->columns(3),

                // Sección 4: Deducciones
                Section::make('Deducciones')
                    ->description('Conceptos que se descuentan al trabajador')
                    ->schema([
                        TextInput::make('health_contribution')
                            ->label('Salud (EPS) 4%')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('pension_contribution')
                            ->label('Pensión 4%')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('unemployment_fund')
                            ->label('Fondo Solidaridad')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('tax_withholding')
                            ->label('Retención Fuente')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('other_deductions')
                            ->label('Otras Deducciones')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn($state, Set $set, Get $get) => self::calculateTotals($set, $get)),
                            
                        TextInput::make('total_deductions')
                            ->label('Total Deducciones')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold']),
                    ])
                    ->columns(3),

                // Sección 5: Neto a Pagar
                Section::make('Resumen')
                    ->schema([
                        TextInput::make('net_payment')
                            ->label('NETO A PAGAR')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                    ])
                    ->columns(1),

                // Sección 6: Opciones
                Section::make('Opciones')
                    ->schema([
                        Toggle::make('sendmail')
                            ->label('Enviar por email al trabajador')
                            ->default(true),
                    ])
                    ->columns(1),
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
                    
                Tables\Columns\TextColumn::make('worker.full_name')
                    ->label('Trabajador')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('period_start_date')
                    ->label('Periodo')
                    ->formatStateUsing(fn(Payroll $record) => 
                        $record->period_start_date->format('d/m/Y') . ' - ' . 
                        $record->period_end_date->format('d/m/Y')
                    ),
                    
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Emisión')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('net_payment')
                    ->label('Neto')
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
                    
                Tables\Columns\IconColumn::make('cune')
                    ->label('CUNE')
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
                    
                Tables\Filters\SelectFilter::make('payroll_type_id')
                    ->label('Tipo Nómina')
                    ->options(PayrollType::getTypes()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(Payroll $record) => $record->is_draft),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function calculateTotals(Set $set, Get $get): void
    {
        // Calcular total devengados
        $totalAccruals = 
            (float) ($get('salary') ?? 0) +
            (float) ($get('transport_allowance') ?? 0) +
            (float) ($get('overtime') ?? 0) +
            (float) ($get('bonuses') ?? 0) +
            (float) ($get('commissions') ?? 0) +
            (float) ($get('severance') ?? 0) +
            (float) ($get('vacation') ?? 0) +
            (float) ($get('other_accruals') ?? 0);

        // Calcular total deducciones
        $totalDeductions = 
            (float) ($get('health_contribution') ?? 0) +
            (float) ($get('pension_contribution') ?? 0) +
            (float) ($get('unemployment_fund') ?? 0) +
            (float) ($get('tax_withholding') ?? 0) +
            (float) ($get('other_deductions') ?? 0);

        // Calcular neto a pagar
        $netPayment = $totalAccruals - $totalDeductions;

        $set('total_accruals', number_format($totalAccruals, 2, '.', ''));
        $set('total_deductions', number_format($totalDeductions, 2, '.', ''));
        $set('net_payment', number_format($netPayment, 2, '.', ''));
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
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'view' => Pages\ViewPayroll::route('/{record}'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
