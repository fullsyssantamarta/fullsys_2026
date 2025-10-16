# Laravel 11 + Filament Multi-Tenant Electronic Invoicing System

## Project Overview
Sistema de facturación electrónica multi-tenant usando Laravel 11 con Filament admin panel, integración con APIDIAN para documentos electrónicos (facturas, nómina, POS) y módulos completos de gestión empresarial.

## Technology Stack
- **Framework**: Laravel 11
- **Admin Panel**: Filament v3
- **Multi-tenancy**: stancl/tenancy
- **API Integration**: APIDIAN (facturación electrónica Colombia)
- **Database**: MySQL/PostgreSQL multi-database tenancy
- **Authentication**: Laravel Sanctum + Filament Auth
- **PDF Generation**: DomPDF
- **Excel**: Maatwebsite Excel
- **Permissions**: Spatie Laravel Permission

## Core Modules
1. **Electronic Invoicing** (Factura Electrónica) - APIDIAN integration
2. **Electronic Payroll** (Nómina Electrónica)
3. **Electronic POS** (Punto de Venta)
4. **Inventory Management** (Inventario)
5. **Accounting** (Contabilidad)
6. **Purchases** (Compras)
7. **Sales** (Ventas)
8. **WhatsApp Integration** (Evolution API)

## Development Progress

### ✅ Completed Steps
- [x] Created workspace structure
- [x] Created Copilot instructions
- [x] Installed Laravel 11 with Filament v3
- [x] Configured multi-tenancy (stancl/tenancy)
- [x] Created separate Admin and Tenant panels
- [x] Created TenantResource in Admin panel with APIDIAN integration
- [x] Created business resources in Tenant panel (Customer, Product, Category, Resolution, Invoice)
- [x] Configured automatic APIDIAN integration
- [x] Created service classes for APIDIAN and WhatsApp
- [x] Created models: Tenant, Customer, Invoice, Product, Category, Resolution
- [x] Configured database migrations
- [x] Installed all dependencies (Composer and NPM)
- [x] Created comprehensive documentation

### 🔄 In Progress
- [ ] Customize Tenant panel resources (forms, validations, relations)
- [ ] Implement complete invoice module with APIDIAN sending

### 📋 Pending Tasks
- [ ] Configure software and resolutions in APIDIAN
- [ ] Create invoice sending functionality
- [ ] Implement payroll module
- [ ] Implement POS module
- [ ] Create inventory management
- [ ] Create accounting module
- [ ] Setup WhatsApp messaging

## Panel Architecture

### Admin Panel (`/admin`)
- **Purpose**: Central tenant management ONLY
- **Resources**: TenantResource (create, edit, configure tenants)
- **Location**: `app/Filament/Resources/`
- **Access**: System administrators

### Tenant Panel (`/tenant/{id}/app`)
- **Purpose**: All business operations for each tenant
- **Resources**: Customer, Product, Category, Resolution, Invoice (and future modules)
- **Location**: `app/Filament/App/Resources/`
- **Access**: Tenant users (isolated by tenant)
- **Middleware**: InitializeTenancyByDomain, PreventAccessFromCentralDomains

## Development Guidelines
- Use Filament v3 for all admin panels
- Implement multi-tenancy at database level
- Follow Laravel 11 best practices
- Admin panel has ONLY TenantResource
- All business resources belong in Tenant panel
- Use Resource classes for Filament CRUD
- Implement proper API service layers
- Use queues for heavy operations (PDF generation, API calls)
- Maintain modular architecture
