<?php

namespace App\Filament\App\Resources;

use App\Enums\PayrollType;
use App\Filament\App\Resources\WorkerResource\Pages;
use App\Models\Worker;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Trabajadores';
    
    protected static ?string $modelLabel = 'Trabajador';
    
    protected static ?string $pluralModelLabel = 'Trabajadores';
    
    protected static ?string $navigationGroup = 'Nómina Electrónica';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Sección 1: Identificación
                Section::make('Información Personal')
                    ->description('Datos de identificación del trabajador')
                    ->schema([
                        Select::make('type_document_id')
                            ->label('Tipo de Documento')
                            ->options([
                                3 => 'Cédula de Ciudadanía',
                                4 => 'Cédula de Extranjería',
                                5 => 'Tarjeta de Identidad',
                                6 => 'NIT',
                                7 => 'Pasaporte',
                            ])
                            ->default(3)
                            ->required(),
                            
                        TextInput::make('identification_number')
                            ->label('Número de Documento')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                            
                        TextInput::make('first_name')
                            ->label('Primer Nombre')
                            ->required()
                            ->maxLength(100),
                            
                        TextInput::make('second_name')
                            ->label('Segundo Nombre')
                            ->maxLength(100),
                            
                        TextInput::make('surname')
                            ->label('Primer Apellido')
                            ->required()
                            ->maxLength(100),
                            
                        TextInput::make('second_surname')
                            ->label('Segundo Apellido')
                            ->maxLength(100),
                    ])
                    ->columns(2),

                // Sección 2: Contacto
                Section::make('Información de Contacto')
                    ->schema([
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->maxLength(150),
                            
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                            
                        TextInput::make('address')
                            ->label('Dirección')
                            ->required()
                            ->maxLength(200)
                            ->columnSpan(2),
                            
                        TextInput::make('municipality_id')
                            ->label('Código DANE Municipio')
                            ->required()
                            ->numeric()
                            ->placeholder('Ej: 11001 (Bogotá)'),
                            
                        TextInput::make('country_code')
                            ->label('Código País')
                            ->default('CO')
                            ->maxLength(2),
                    ])
                    ->columns(2),

                // Sección 3: Información Laboral
                Section::make('Información Laboral')
                    ->schema([
                        Select::make('type_worker_id')
                            ->label('Tipo de Trabajador')
                            ->options(PayrollType::getWorkerTypes())
                            ->required(),
                            
                        Select::make('subtype_worker_id')
                            ->label('Subtipo de Trabajador')
                            ->options(PayrollType::getWorkerSubtypes())
                            ->default(0),
                            
                        Select::make('type_contract_id')
                            ->label('Tipo de Contrato')
                            ->options(PayrollType::getContractTypes())
                            ->required(),
                            
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                                'retired' => 'Retirado',
                            ])
                            ->default('active')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('hire_date')
                            ->label('Fecha de Contratación'),
                            
                        Forms\Components\DatePicker::make('retirement_date')
                            ->label('Fecha de Retiro'),
                            
                        Toggle::make('high_risk_pension')
                            ->label('Alto Riesgo Pensión')
                            ->default(false),
                            
                        Toggle::make('integral_salary')
                            ->label('Salario Integral')
                            ->default(false),
                    ])
                    ->columns(2),

                // Sección 4: Salario y Cuenta Bancaria
                Section::make('Información Salarial y Bancaria')
                    ->schema([
                        TextInput::make('salary')
                            ->label('Salario Mensual')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                            
                        TextInput::make('bank_name')
                            ->label('Banco')
                            ->maxLength(100),
                            
                        Select::make('account_type')
                            ->label('Tipo de Cuenta')
                            ->options([
                                'Ahorros' => 'Ahorros',
                                'Corriente' => 'Corriente',
                            ]),
                            
                        TextInput::make('account_number')
                            ->label('Número de Cuenta')
                            ->maxLength(50),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identification_number')
                    ->label('Documento')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nombre Completo')
                    ->searchable(['first_name', 'surname'])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono'),
                    
                Tables\Columns\TextColumn::make('salary')
                    ->label('Salario')
                    ->money('COP')
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'retired',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'retired' => 'Retirado',
                        default => $state,
                    }),
                    
                Tables\Columns\IconColumn::make('integral_salary')
                    ->label('Salario Integral')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'retired' => 'Retirado',
                    ]),
                    
                Tables\Filters\SelectFilter::make('type_worker_id')
                    ->label('Tipo Trabajador')
                    ->options(PayrollType::getWorkerTypes()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWorkers::route('/'),
            'create' => Pages\CreateWorker::route('/create'),
            'view' => Pages\ViewWorker::route('/{record}'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }
}
