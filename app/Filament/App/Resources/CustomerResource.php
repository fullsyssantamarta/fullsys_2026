<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\CustomerResource\Pages;
use App\Filament\App\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Clientes';
    
    protected static ?string $modelLabel = 'Cliente';
    
    protected static ?string $pluralModelLabel = 'Clientes';
    
    protected static ?string $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información General')
                    ->schema([
                        Forms\Components\Select::make('type_document_identification_id')
                            ->label('Tipo de Documento')
                            ->options([
                                '11' => 'Registro civil',
                                '12' => 'Tarjeta de identidad',
                                '13' => 'Cédula de ciudadanía',
                                '21' => 'Tarjeta de extranjería',
                                '22' => 'Cédula de extranjería',
                                '31' => 'NIT',
                                '41' => 'Pasaporte',
                                '42' => 'Documento de identificación extranjero',
                                '50' => 'NIT de otro país',
                            ])
                            ->required()
                            ->default('13')
                            ->searchable(),
                            
                        Forms\Components\TextInput::make('identification_number')
                            ->label('Número de Identificación')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                            
                        Forms\Components\TextInput::make('dv')
                            ->label('Dígito de Verificación')
                            ->maxLength(1)
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('type_document_identification_id') === '31'),
                            
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo / Razón Social')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                            
                        Forms\Components\TextInput::make('mobile')
                            ->label('Celular')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Información Tributaria')
                    ->schema([
                        Forms\Components\Select::make('type_organization_id')
                            ->label('Tipo de Organización')
                            ->options([
                                '1' => 'Persona Jurídica',
                                '2' => 'Persona Natural',
                            ])
                            ->required()
                            ->default('2'),
                            
                        Forms\Components\Select::make('type_regime_id')
                            ->label('Régimen Tributario')
                            ->options([
                                '1' => 'Responsable de IVA',
                                '2' => 'No Responsable de IVA',
                                '3' => 'Responsable Simplificado',
                            ])
                            ->required()
                            ->default('2'),
                            
                        Forms\Components\Select::make('type_liability_id')
                            ->label('Responsabilidades Fiscales')
                            ->options([
                                'O-13' => 'Gran contribuyente',
                                'O-15' => 'Autorretenedor',
                                'O-23' => 'Agente de retención IVA',
                                'O-47' => 'Régimen simple de tributación',
                                'R-99-PN' => 'No aplica - Persona Natural',
                            ])
                            ->multiple()
                            ->searchable(),
                            
                        Forms\Components\Toggle::make('is_company')
                            ->label('¿Es Empresa?')
                            ->default(false),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Dirección')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(100),
                            
                        Forms\Components\TextInput::make('department')
                            ->label('Departamento')
                            ->maxLength(100),
                            
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Código Postal')
                            ->maxLength(10),
                            
                        Forms\Components\Select::make('country')
                            ->label('País')
                            ->options([
                                'CO' => 'Colombia',
                                'US' => 'Estados Unidos',
                                'MX' => 'México',
                                'VE' => 'Venezuela',
                                'EC' => 'Ecuador',
                                'PE' => 'Perú',
                            ])
                            ->default('CO')
                            ->searchable(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                            
                        Forms\Components\Toggle::make('active')
                            ->label('Activo')
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('identification_number')
                    ->label('Identificación')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre / Razón Social')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_document_identification_id')
                    ->label('Tipo de Documento')
                    ->options([
                        '13' => 'Cédula de ciudadanía',
                        '31' => 'NIT',
                        '41' => 'Pasaporte',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
                    ->label('Crear Cliente'),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
