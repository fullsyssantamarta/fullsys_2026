# 📋 Arquitectura Multi-Panel - Documentación Técnica

## 🎯 Problema Resuelto

El sistema inicialmente tenía todos los recursos (Customer, Product, Invoice, etc.) en el panel administrativo, cuando en realidad estos deberían estar **solo en los paneles de cada tenant**.

**Arquitectura Incorrecta**:
```
/admin
  ├── TenantResource ✅
  ├── CustomerResource ❌ (No debe estar aquí)
  ├── ProductResource ❌ (No debe estar aquí)
  └── InvoiceResource ❌ (No debe estar aquí)
```

**Arquitectura Correcta**:
```
/admin (Panel Administrativo Central)
  └── TenantResource ✅ (Solo gestión de tenants)

/tenant/{tenant}/app (Panel de cada Tenant)
  ├── CustomerResource ✅
  ├── ProductResource ✅
  ├── CategoryResource ✅
  ├── ResolutionResource ✅
  └── InvoiceResource ✅
```

## 🔧 Solución Implementada

### 1. Creación del Panel de Tenant

Se creó un nuevo panel Filament específico para operaciones de tenant:

```bash
php artisan make:filament-panel app
```

Esto generó:
- `app/Providers/Filament/AppPanelProvider.php`
- Registro automático en `bootstrap/providers.php`

### 2. Configuración del Panel con Stancl/Tenancy

**Archivo**: `app/Providers/Filament/AppPanelProvider.php`

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('app')
        ->path('app')
        ->tenant(\App\Models\Tenant::class)              // ⭐ Configuración multi-tenant
        ->tenantRoutePrefix('tenant')                    // ⭐ Prefijo de ruta
        ->login()
        ->brandName('Sistema de Facturación')
        ->colors(['primary' => Color::Blue])
        ->middleware([
            // Middlewares estándar de Laravel
            EncryptCookies::class,
            StartSession::class,
            VerifyCsrfToken::class,
            // ... otros middlewares ...
            
            // ⭐ Middlewares de Tenancy
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ])
        ->databaseNotifications();
}
```

**Características clave**:
- ✅ Identificación automática del tenant por dominio
- ✅ Aislamiento completo de datos entre tenants
- ✅ Prevención de acceso desde dominios centrales
- ✅ Soporte para notificaciones de base de datos

### 3. Eliminación de Recursos del Panel Admin

Se eliminaron los siguientes archivos y directorios del panel administrativo:

```bash
rm -f app/Filament/Resources/CustomerResource.php
rm -f app/Filament/Resources/ProductResource.php
rm -f app/Filament/Resources/CategoryResource.php
rm -f app/Filament/Resources/ResolutionResource.php
rm -f app/Filament/Resources/InvoiceResource.php

rm -rf app/Filament/Resources/CustomerResource/
rm -rf app/Filament/Resources/ProductResource/
rm -rf app/Filament/Resources/CategoryResource/
rm -rf app/Filament/Resources/ResolutionResource/
rm -rf app/Filament/Resources/InvoiceResource/
```

### 4. Creación de Recursos en Panel de Tenant

Se recrearon los recursos en el panel de tenant:

```bash
php artisan make:filament-resource Customer --panel=app --generate --view
php artisan make:filament-resource Product --panel=app --generate --view
php artisan make:filament-resource Category --panel=app --generate --view
php artisan make:filament-resource Resolution --panel=app --generate --view
php artisan make:filament-resource Invoice --panel=app --generate --view
```

**Ubicación nueva**: `app/Filament/App/Resources/`

Cada recurso incluye:
- Resource principal (ej: `CustomerResource.php`)
- Páginas CRUD (List, Create, Edit, View)
- Configuración automática de formularios y tablas

## 📊 Estructura de Directorios Actual

```
app/
└── Filament/
    ├── Resources/                    # Panel Admin
    │   └── TenantResource.php        # ✅ Solo gestión de tenants
    │       ├── Pages/
    │       │   ├── CreateTenant.php
    │       │   ├── EditTenant.php
    │       │   └── ListTenants.php
    │       └── RelationManagers/
    │           └── DomainsRelationManager.php
    │
    └── App/                          # Panel Tenant
        └── Resources/                # ✅ Recursos de negocio
            ├── CustomerResource.php
            │   └── Pages/
            │       ├── CreateCustomer.php
            │       ├── EditCustomer.php
            │       ├── ListCustomers.php
            │       └── ViewCustomer.php
            ├── ProductResource.php
            │   └── Pages/
            ├── CategoryResource.php
            │   └── Pages/
            ├── ResolutionResource.php
            │   └── Pages/
            └── InvoiceResource.php
                └── Pages/
```

## 🌐 Rutas y Acceso

### Panel Administrativo

```
URL: http://localhost:8000/admin
Propósito: Gestión de tenants (crear, editar, configurar)
Middleware: Sin tenancy, acceso central
```

### Panel de Tenant

```
URL: http://localhost:8000/tenant/{tenant-id}/app
Propósito: Operaciones de negocio del tenant
Middleware: Con tenancy, datos aislados por tenant
```

**Ejemplo de acceso**:
```
http://localhost:8000/tenant/1/app/customers
http://localhost:8000/tenant/1/app/products
http://localhost:8000/tenant/1/app/invoices
```

## 🔐 Seguridad y Aislamiento

### Middleware de Tenancy

**InitializeTenancyByDomain**:
- Identifica el tenant actual basado en el dominio
- Configura la conexión de base de datos correcta
- Inyecta el tenant en el contexto de la aplicación

**PreventAccessFromCentralDomains**:
- Previene acceso a recursos de tenant desde dominio central
- Evita fugas de datos entre tenants

### Scoping Automático

Todos los queries en el panel de tenant están automáticamente scopeados:

```php
// En el contexto de tenant, automáticamente filtra por tenant_id
Customer::all();  // Solo clientes del tenant actual
Product::all();   // Solo productos del tenant actual
Invoice::all();   // Solo facturas del tenant actual
```

## 🔄 Flujo de Trabajo

### 1. Administrador Central

1. Accede a `/admin`
2. Crea nuevo tenant con datos fiscales y APIDIAN
3. El sistema automáticamente:
   - Crea base de datos del tenant
   - Registra empresa en APIDIAN
   - Configura token de autenticación
   - Crea dominio(s) del tenant

### 2. Usuario del Tenant

1. Accede a `/tenant/{id}/app` o por su dominio personalizado
2. Autenticación en contexto del tenant
3. Acceso solo a datos de su empresa
4. Operaciones de facturación, inventario, etc.

## 📦 Integración con APIDIAN

El flujo de integración APIDIAN se mantiene en el panel administrativo:

**TenantResource → CreateTenant → afterCreate()**:

```php
protected function afterCreate(): void
{
    $tenant = $this->record;
    
    try {
        $apidianService = app(ApidianService::class);
        $response = $apidianService->configureCompany(
            $tenant->nit,
            $tenant->dv,
            [
                'business_name' => $tenant->business_name,
                'trade_name' => $tenant->trade_name,
                // ... más configuración
            ]
        );
        
        // Guardar token y configuración
        $tenant->update([
            'apidian_token' => $response['token'],
            'apidian_test_set_id' => $response['testSetId'],
            'apidian_configured' => true
        ]);
        
        Notification::make()
            ->success()
            ->title('Empresa configurada en APIDIAN')
            ->send();
            
    } catch (\Exception $e) {
        Notification::make()
            ->danger()
            ->title('Error al configurar APIDIAN: ' . $e->getMessage())
            ->send();
    }
}
```

## 🎨 Personalización por Panel

### Panel Admin
- Color primario: Amber
- Widgets: Account + FilamentInfo
- Sin tenant context

### Panel Tenant
- Color primario: Blue
- Widgets: Account only
- Con tenant context
- Database notifications activadas

## 🚀 Próximos Pasos

Con la arquitectura de paneles correctamente implementada, los siguientes pasos son:

1. **Personalizar Recursos de Tenant**:
   - Ajustar campos de formularios
   - Configurar validaciones
   - Agregar relaciones

2. **Implementar Módulo de Facturación**:
   - Envío de facturas a APIDIAN
   - Descarga de PDFs
   - Notificaciones WhatsApp

3. **Crear Usuarios por Tenant**:
   - Sistema de roles y permisos
   - Autenticación separada por tenant

4. **Configurar Dominios Personalizados**:
   - Mapeo de subdominios
   - SSL por tenant

## 📝 Comandos Útiles

```bash
# Ver paneles registrados
php artisan filament:list

# Crear usuario admin central
php artisan make:filament-user

# Crear recurso en panel admin
php artisan make:filament-resource NombreModelo

# Crear recurso en panel tenant
php artisan make:filament-resource NombreModelo --panel=app

# Limpiar caché de Filament
php artisan filament:cache-components
```

## ✅ Checklist de Implementación

- [x] Panel administrativo separado
- [x] Panel de tenant creado
- [x] Middleware de tenancy configurado
- [x] Recursos movidos a panel correcto
- [x] Integración APIDIAN mantenida
- [x] Documentación actualizada
- [ ] Personalización de recursos
- [ ] Testing de multi-tenancy
- [ ] Configuración de dominios

---

**Fecha de implementación**: 2025-01-20
**Versión**: 1.0
**Estado**: ✅ Completado
