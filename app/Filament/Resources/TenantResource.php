<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Support\Enums\FontWeight;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Empresas (Tenants)';
    
    protected static ?string $modelLabel = 'Empresa';
    
    protected static ?string $pluralModelLabel = 'Empresas';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Información General')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre Comercial')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                    
                                Forms\Components\TextInput::make('business_name')
                                    ->label('Razón Social')
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                    
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('nit')
                                            ->label('NIT')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(20),
                                            
                                        Forms\Components\TextInput::make('dv')
                                            ->label('DV')
                                            ->maxLength(1)
                                            ->numeric(),
                                            
                                        Forms\Components\Select::make('status')
                                            ->label('Estado')
                                            ->options([
                                                'trial' => 'Prueba',
                                                'active' => 'Activo',
                                                'inactive' => 'Inactivo',
                                                'suspended' => 'Suspendido',
                                            ])
                                            ->default('trial')
                                            ->required(),
                                    ]),
                                    
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                            
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20),
                                    ]),
                                    
                                Forms\Components\Textarea::make('address')
                                    ->label('Dirección')
                                    ->rows(2)
                                    ->columnSpan(2),
                                    
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('city')
                                            ->label('Ciudad')
                                            ->maxLength(100),
                                            
                                        Forms\Components\TextInput::make('state')
                                            ->label('Departamento')
                                            ->maxLength(100),
                                            
                                        Forms\Components\TextInput::make('country')
                                            ->label('País')
                                            ->default('Colombia')
                                            ->maxLength(100),
                                    ]),
                                    
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('tenants/logos')
                                    ->columnSpan(2),
                            ])
                            ->columns(2),
                            
                        Tabs\Tab::make('Información Tributaria')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('type_document_identification_id')
                                            ->label('Tipo de Documento')
                                            ->numeric()
                                            ->helperText('ID del tipo de documento en APIDIAN'),
                                            
                                        Forms\Components\TextInput::make('type_organization_id')
                                            ->label('Tipo de Organización')
                                            ->numeric()
                                            ->helperText('ID del tipo de organización en APIDIAN'),
                                            
                                        Forms\Components\TextInput::make('type_regime_id')
                                            ->label('Tipo de Régimen')
                                            ->numeric()
                                            ->helperText('ID del tipo de régimen en APIDIAN'),
                                            
                                        Forms\Components\TextInput::make('type_liability_id')
                                            ->label('Tipo de Responsabilidad')
                                            ->numeric()
                                            ->helperText('ID del tipo de responsabilidad en APIDIAN'),
                                            
                                        Forms\Components\TextInput::make('municipality_id')
                                            ->label('ID Municipio')
                                            ->numeric()
                                            ->helperText('ID del municipio en APIDIAN'),
                                            
                                        Forms\Components\TextInput::make('merchant_registration')
                                            ->label('Matrícula Mercantil')
                                            ->maxLength(50),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Configuración APIDIAN')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Placeholder::make('apidian_info')
                                    ->label('')
                                    ->content('Esta información se configurará automáticamente al crear/actualizar la empresa.')
                                    ->columnSpan(2),
                                    
                                Forms\Components\Select::make('apidian_environment')
                                    ->label('Ambiente APIDIAN')
                                    ->options([
                                        'test' => 'Pruebas (Habilitación)',
                                        'production' => 'Producción',
                                    ])
                                    ->default('test')
                                    ->required()
                                    ->columnSpan(2),
                                    
                                Forms\Components\Textarea::make('apidian_token')
                                    ->label('Token APIDIAN')
                                    ->disabled()
                                    ->rows(3)
                                    ->helperText('Se genera automáticamente al configurar la empresa en APIDIAN')
                                    ->columnSpan(2),
                                    
                                Forms\Components\Placeholder::make('apidian_configured_at')
                                    ->label('Configurado en APIDIAN')
                                    ->content(fn ($record) => $record?->apidian_configured_at?->format('d/m/Y H:i') ?? 'No configurado'),
                            ])
                            ->columns(2),
                            
                        Tabs\Tab::make('Configuración Email')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Forms\Components\TextInput::make('mail_host')
                                    ->label('Servidor SMTP')
                                    ->maxLength(255)
                                    ->helperText('Ej: smtp.gmail.com'),
                                    
                                Forms\Components\TextInput::make('mail_port')
                                    ->label('Puerto')
                                    ->maxLength(10)
                                    ->helperText('Ej: 587'),
                                    
                                Forms\Components\TextInput::make('mail_username')
                                    ->label('Usuario')
                                    ->email()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('mail_password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->maxLength(255)
                                    ->revealable(),
                                    
                                Forms\Components\Select::make('mail_encryption')
                                    ->label('Encriptación')
                                    ->options([
                                        'tls' => 'TLS',
                                        'ssl' => 'SSL',
                                    ])
                                    ->default('tls'),
                            ])
                            ->columns(2),
                            
                        Tabs\Tab::make('WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Toggle::make('whatsapp_enabled')
                                    ->label('Habilitar WhatsApp')
                                    ->default(false)
                                    ->columnSpan(2),
                                    
                                Forms\Components\TextInput::make('whatsapp_instance')
                                    ->label('Instancia WhatsApp')
                                    ->maxLength(255)
                                    ->helperText('Nombre de la instancia en Evolution API')
                                    ->columnSpan(2),
                            ]),
                            
                        Tabs\Tab::make('Plan y Suscripción')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Select::make('plan')
                                    ->label('Plan')
                                    ->options([
                                        'trial' => 'Prueba',
                                        'basic' => 'Básico',
                                        'professional' => 'Profesional',
                                        'enterprise' => 'Empresarial',
                                    ])
                                    ->default('trial')
                                    ->required(),
                                    
                                Forms\Components\DateTimePicker::make('trial_ends_at')
                                    ->label('Fin de Prueba')
                                    ->default(now()->addDays(30)),
                                    
                                Forms\Components\DateTimePicker::make('subscription_ends_at')
                                    ->label('Fin de Suscripción'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-company.png')),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                    
                Tables\Columns\TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->nit . '-' . $record->dv),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                    
                Tables\Columns\TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'trial',
                        'success' => 'active',
                        'danger' => 'inactive',
                        'secondary' => 'suspended',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'trial',
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-x-circle' => 'inactive',
                        'heroicon-o-pause-circle' => 'suspended',
                    ]),
                    
                Tables\Columns\BadgeColumn::make('plan')
                    ->label('Plan')
                    ->colors([
                        'secondary' => 'trial',
                        'info' => 'basic',
                        'success' => 'professional',
                        'warning' => 'enterprise',
                    ]),
                    
                Tables\Columns\IconColumn::make('apidian_token')
                    ->label('APIDIAN')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !empty($record->apidian_token))
                    ->tooltip(fn ($record) => $record->apidian_token ? 'Configurado' : 'No configurado'),
                    
                Tables\Columns\IconColumn::make('whatsapp_enabled')
                    ->label('WhatsApp')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'trial' => 'Prueba',
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido',
                    ]),
                    
                Tables\Filters\SelectFilter::make('plan')
                    ->label('Plan')
                    ->options([
                        'trial' => 'Prueba',
                        'basic' => 'Básico',
                        'professional' => 'Profesional',
                        'enterprise' => 'Empresarial',
                    ]),
                    
                Tables\Filters\Filter::make('apidian_configured')
                    ->label('Con APIDIAN configurado')
                    ->query(fn (Builder $query) => $query->whereNotNull('apidian_token')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('configure_apidian')
                    ->label('Configurar APIDIAN')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // La acción se manejará en la página de creación/edición
                    })
                    ->visible(fn ($record) => empty($record->apidian_token)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
