# 🏢 Fullsys - Sistema de Facturación Electrónica Multi-Tenant# 🏢 Fullsys - Sistema de Facturación Electrónica Multi-Tenant<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



Sistema completo de facturación electrónica multi-tenant construido con Laravel 11 y Filament v3, con integración a APIDIAN para documentos electrónicos de Colombia.



## 📋 Características PrincipalesSistema completo de facturación electrónica multi-tenant construido con Laravel 11 y Filament v3, con integración a APIDIAN para documentos electrónicos de Colombia.<p align="center">



### ✅ Módulos Implementados<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>



- ✨ **Panel de Administración Central** con Filament v3## 📋 Características Principales<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

- 🏢 **Gestión de Empresas (Tenants)** con multi-database

- 📄 **Integración Automática con APIDIAN**<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

- 📧 **Configuración de Email por Tenant**

- 💬 **Integración WhatsApp** (Evolution API)### ✅ Módulos Implementados<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

- 👥 **Sistema de Permisos** (Spatie Laravel Permission)

- 📊 **Generación de PDF** (DomPDF)- ✨ **Panel de Administración Central** con Filament v3</p>

- 📤 **Exportación Excel** (Maatwebsite Excel)

- 🏢 **Gestión de Empresas (Tenants)** con multi-database

### 🚀 Módulos en Desarrollo

- 📄 **Integración Automática con APIDIAN**## About Laravel

- 📑 Factura Electrónica

- 💰 Nómina Electrónica  - 📧 **Configuración de Email por Tenant**

- 🛒 POS Electrónico

- 📦 Inventario- 💬 **Integración WhatsApp** (Evolution API)Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- 📚 Contabilidad

- 🛍️ Compras y Ventas- 👥 **Sistema de Permisos** (Spatie Laravel Permission)



## 🛠️ Stack Tecnológico- 📊 **Generación de PDF** (DomPDF)- [Simple, fast routing engine](https://laravel.com/docs/routing).



```- 📤 **Exportación Excel** (Maatwebsite Excel)- [Powerful dependency injection container](https://laravel.com/docs/container).

Framework:       Laravel 11

Admin Panel:     Filament v3- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

Multi-tenancy:   stancl/tenancy

PHP Version:     8.3+### 🚀 Módulos en Desarrollo- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

Database:        MySQL/PostgreSQL

API Client:      Guzzle HTTP- 📑 Factura Electrónica- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

```

- 💰 Nómina Electrónica  - [Robust background job processing](https://laravel.com/docs/queues).

### 📦 Paquetes Principales

- 🛒 POS Electrónico- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- **filament/filament** ^3.3 - Panel de administración moderno

- **stancl/tenancy** ^3.9 - Multi-tenancy con base de datos separada- 📦 Inventario

- **spatie/laravel-permission** ^6.21 - Control de roles y permisos

- **barryvdh/laravel-dompdf** ^3.1 - Generación de PDFs- 📚 ContabilidadLaravel is accessible, powerful, and provides tools required for large, robust applications.

- **maatwebsite/excel** ^3.1 - Importación/Exportación Excel

- 🛍️ Compras y Ventas

## 🏗️ Arquitectura

## Learning Laravel

### Paneles Filament

## 🛠️ Stack Tecnológico

El sistema utiliza **dos paneles** Filament separados:

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

#### 1. Panel Administrativo (`/admin`)

- **Propósito**: Gestión de tenants únicamente```

- **Acceso**: Administradores del sistema central

- **Recursos disponibles**:Framework:       Laravel 11You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

  - TenantResource: CRUD completo de tenants con integración automática APIDIAN

- **Ubicación**: `app/Filament/Resources/`Admin Panel:     Filament v3



#### 2. Panel de Tenant (`/tenant/{tenant}/app`)Multi-tenancy:   stancl/tenancyIf you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

- **Propósito**: Gestión de todas las operaciones de negocio de cada tenant

- **Acceso**: Por dominio/subdominio del tenantPHP Version:     8.3+

- **Recursos disponibles**:

  - CustomerResource: Gestión de clientesDatabase:        MySQL/PostgreSQL## Laravel Sponsors

  - ProductResource: Gestión de productos e inventario

  - CategoryResource: Categorías de productosAPI Client:      Guzzle HTTP

  - ResolutionResource: Resoluciones de facturación DIAN

  - InvoiceResource: Facturación electrónica```We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

  - (Futuros: Nómina, POS, Contabilidad, etc.)

- **Ubicación**: `app/Filament/App/Resources/`

- **Configuración**: 

  - Middleware de tenancy automático### 📦 Paquetes Principales### Premium Partners

  - Aislamiento completo por base de datos

  - Integración con APIDIAN y Evolution API



### Multi-tenancy con Stancl/Tenancy- **filament/filament** ^3.3 - Panel de administración moderno- **[Vehikl](https://vehikl.com)**



- **Estrategia**: Base de datos separada por tenant- **stancl/tenancy** ^3.9 - Multi-tenancy con base de datos separada- **[Tighten Co.](https://tighten.co)**

- **Creación automática**: Al crear un tenant se genera su BD

- **Middleware**: Identificación automática por dominio- **spatie/laravel-permission** ^6.21 - Control de roles y permisos- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

- **Dominios**: Cada tenant puede tener múltiples dominios/subdominios

- **barryvdh/laravel-dompdf** ^3.1 - Generación de PDFs- **[64 Robots](https://64robots.com)**

### Base de Datos

- **maatwebsite/excel** ^3.1 - Importación/Exportación Excel- **[Curotec](https://www.curotec.com/services/technologies/laravel)**

```

📊 Base de Datos Central- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

   ├── tenants (empresas)

   ├── domains (dominios de tenants)## 📥 Instalación- **[Redberry](https://redberry.international/laravel-development)**

   └── users (administradores centrales)

- **[Active Logic](https://activelogic.com)**

📊 Base de Datos por Tenant (tenant_{id})

   ├── customers (clientes)### Requisitos Previos

   ├── products (productos)

   ├── categories (categorías)## Contributing

   ├── invoices (facturas)

   ├── invoice_items (items de factura)```bash

   ├── resolutions (resoluciones DIAN)

   ├── users (usuarios del tenant)- PHP >= 8.3Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

   └── permissions/roles

```- Composer



## 📂 Estructura del Proyecto- MySQL/PostgreSQL## Code of Conduct



```- Node.js >= 18 y NPM

fullsys_2026/

├── app/- GitIn order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

│   ├── Filament/

│   │   ├── Resources/               # Panel Admin (solo Tenants)```

│   │   │   └── TenantResource.php

│   │   └── App/## Security Vulnerabilities

│   │       └── Resources/           # Panel Tenant (negocio)

│   │           ├── CustomerResource.php### Paso 1: Clonar el Repositorio

│   │           ├── ProductResource.php

│   │           ├── CategoryResource.phpIf you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

│   │           ├── ResolutionResource.php

│   │           └── InvoiceResource.php```bash

│   ├── Models/

│   │   ├── Tenant.php              # Modelo central de tenantgit clone https://github.com/tu-usuario/fullsys-2026.git## License

│   │   ├── Customer.php

│   │   ├── Product.phpcd fullsys-2026

│   │   ├── Category.php

│   │   ├── Invoice.php```The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

│   │   ├── InvoiceItem.php

│   │   └── Resolution.php

│   ├── Services/### Paso 2: Instalar Dependencias

│   │   ├── Apidian/

│   │   │   └── ApidianService.php  # Integración APIDIAN```bash

│   │   └── WhatsApp/# Dependencias PHP

│   │       └── EvolutionApiService.php  # Integración WhatsAppcomposer install

│   └── Providers/

│       └── Filament/# Dependencias JavaScript

│           ├── AdminPanelProvider.php   # Configuración panel adminnpm install

│           └── AppPanelProvider.php     # Configuración panel tenant```

├── config/

│   ├── apidian.php                 # Configuración APIDIAN### Paso 3: Configurar Variables de Entorno

│   ├── services.php                # APIs externas

│   └── tenancy.php                 # Configuración multi-tenancy```bash

├── database/# Copiar archivo de ejemplo

│   └── migrations/cp .env.example .env

│       └── 2019_09_15_000010_create_tenants_table.php

└── routes/# Generar key de aplicación

    └── web.phpphp artisan key:generate

``````



## 📥 Instalación### Paso 4: Configurar Base de Datos



### Requisitos PreviosEdita `.env` con tus credenciales:



```bash```env

- PHP >= 8.3DB_CONNECTION=mysql

- ComposerDB_HOST=127.0.0.1

- Node.js & NPMDB_PORT=3306

- MySQL/PostgreSQLDB_DATABASE=fullsys_central

```DB_USERNAME=root

DB_PASSWORD=tu_password

### Pasos de Instalación```



Ver el archivo [INSTALACION.md](INSTALACION.md) para instrucciones detalladas.### Paso 5: Ejecutar Migraciones



Resumen rápido:```bash

# Migrar base de datos central

```bashphp artisan migrate

# 1. Clonar repositorio

git clone <repo-url> fullsys_2026# Migrar seeds (opcional)

cd fullsys_2026php artisan db:seed

```

# 2. Instalar dependencias

composer install### Paso 6: Configurar APIDIAN

npm install

Edita `.env` con tus credenciales de APIDIAN:

# 3. Configurar .env

cp .env.example .env```env

php artisan key:generateAPIDIAN_BASE_URL=https://api.apidian.com/api

APIDIAN_TOKEN=tu_token_aqui

# 4. Configurar base de datos en .envAPIDIAN_ENVIRONMENT=test

# DB_CONNECTION=mysql```

# DB_HOST=127.0.0.1

# DB_PORT=3306### Paso 7: Compilar Assets

# DB_DATABASE=fullsys_central

# DB_USERNAME=root```bash

# DB_PASSWORD=npm run build

```

# 5. Ejecutar migraciones

php artisan migrate### Paso 8: Crear Usuario Administrador



# 6. Compilar assets```bash

npm run devphp artisan make:filament-user

```

# 7. Iniciar servidor

php artisan serveIngresa los datos solicitados:

```- Nombre

- Email

## 🔑 Acceso al Sistema- Password



### Panel Administrativo### Paso 9: Iniciar Servidor



``````bash

URL:      http://localhost:8000/adminphp artisan serve

Usuario:  Crear con: php artisan make:filament-user```

```

Accede a: `http://localhost:8000/admin`

### Panel de Tenant

## 🏗️ Arquitectura

```

URL:      http://localhost:8000/tenant/{tenant-id}/app### Paneles Filament

Acceso:   Requiere dominio configurado para el tenant

```El sistema utiliza **dos paneles** Filament separados:



## 🔌 Integración APIDIAN#### 1. Panel Administrativo (`/admin`)

- **Propósito**: Gestión de tenants únicamente

### Configuración Automática- **Acceso**: Administradores del sistema central

- **Recursos disponibles**:

Al crear un tenant desde el panel administrativo, el sistema automáticamente:  - TenantResource: CRUD completo de tenants con integración automática APIDIAN

- **Ubicación**: `app/Filament/Resources/`

1. ✅ Registra la empresa en APIDIAN

2. ✅ Configura el software#### 2. Panel de Tenant (`/app`)

3. ✅ Obtiene el testSetId- **Propósito**: Gestión de todas las operaciones de negocio de cada tenant

4. ✅ Guarda el token de autenticación- **Acceso**: Por dominio/subdominio del tenant

- **Recursos disponibles**:

### Endpoints APIDIAN Utilizados  - CustomerResource: Gestión de clientes

  - ProductResource: Gestión de productos e inventario

```  - CategoryResource: Categorías de productos

POST   /ubl2.1/config/{nit}/{dv}           - Configurar empresa  - ResolutionResource: Resoluciones de facturación DIAN

POST   /ubl2.1/invoice                      - Enviar factura  - InvoiceResource: Facturación electrónica

POST   /ubl2.1/credit-note                  - Nota crédito  - (Futuros: Nómina, POS, Contabilidad, etc.)

POST   /ubl2.1/debit-note                   - Nota débito- **Ubicación**: `app/Filament/App/Resources/`

POST   /ubl2.1/payroll                      - Nómina electrónica- **Configuración**: 

GET    /ubl2.1/status/{documentKey}         - Estado documento  - Middleware de tenancy automático

GET    /ubl2.1/pdf/{documentKey}            - Descargar PDF  - Aislamiento completo por base de datos

```  - Integración con APIDIAN y Evolution API



## 💬 Integración WhatsApp (Evolution API)### Multi-tenancy con Stancl/Tenancy



### Configuración



```env```

EVOLUTION_API_URL=https://api.evolutionapi.comfullsys_2026/

EVOLUTION_API_KEY=tu-api-key├── app/

```│   ├── Filament/

│   │   ├── Resources/

### Funcionalidades│   │   │   └── TenantResource.php      # Gestión de empresas

│   │   └── Providers/

- Envío de mensajes de texto│   │       └── AdminPanelProvider.php   # Configuración panel admin

- Envío de facturas PDF│   ├── Models/

- Verificación de estado│   │   ├── Tenant.php                   # Modelo de empresa/tenant

- Notificaciones automáticas│   │   ├── Customer.php                 # Modelo de clientes

│   │   ├── Invoice.php                  # Modelo de facturas

## 🗺️ Roadmap│   │   ├── Product.php                  # Modelo de productos

│   │   └── ...

### Fase 1 - Completado ✅│   └── Services/

- [x] Instalación Laravel 11│       ├── Apidian/

- [x] Configuración Filament v3│       │   └── ApidianService.php       # Servicio integración APIDIAN

- [x] Multi-tenancy (stancl/tenancy)│       └── WhatsApp/

- [x] Panel administrativo de tenants│           └── EvolutionApiService.php  # Servicio WhatsApp

- [x] Integración APIDIAN├── config/

- [x] Servicio WhatsApp│   ├── apidian.php                      # Configuración APIDIAN

- [x] Modelos base│   ├── tenancy.php                      # Configuración multi-tenancy

- [x] Panel de tenant separado│   └── permission.php                   # Configuración permisos

├── database/

### Fase 2 - En Progreso 🚧│   └── migrations/

- [ ] Módulo de Facturación Electrónica completo│       ├── 2019_09_15_000010_create_tenants_table.php

- [ ] Gestión de clientes│       ├── 2025_10_16_000028_create_permission_tables.php

- [ ] Gestión de productos│       └── ...

- [ ] Envío de facturas a APIDIAN└── routes/

- [ ] Descarga de PDFs    └── web.php

- [ ] Notificaciones WhatsApp```



### Fase 3 - Planificado 📋## 🔧 Configuración

- [ ] Nómina Electrónica

- [ ] POS Electrónico### Multi-Tenancy

- [ ] Módulo de Inventario

- [ ] Módulo de ContabilidadEl sistema utiliza **base de datos separada por tenant**:

- [ ] Módulo de Compras

- [ ] Módulo de Ventas- **Base de datos central**: Gestiona los tenants y configuración global

- [ ] Reportes y estadísticas- **Bases de datos tenant**: Cada empresa tiene su propia BD (automático)



## 🤝 Contribuir```php

// Configuración en config/tenancy.php

Si deseas contribuir al proyecto:'database' => [

    'prefix' => 'tenant_',

1. Fork del repositorio    'suffix' => '',

2. Crear rama feature (`git checkout -b feature/AmazingFeature`)],

3. Commit cambios (`git commit -m 'Add: AmazingFeature'`)```

4. Push a la rama (`git push origin feature/AmazingFeature`)

5. Abrir Pull Request### APIDIAN - Configuración de Empresa



## 📝 LicenciaCuando creas un nuevo tenant en el panel, el sistema automáticamente:



Este proyecto es software privado. Todos los derechos reservados.1. ✅ Valida los datos de la empresa

2. 📡 Registra la empresa en APIDIAN

## 📞 Soporte3. 🔑 Guarda el token de autenticación

4. ✉️ Configura el servidor de correo (si se proporciona)

Para soporte técnico o consultas:5. 🗄️ Crea la base de datos del tenant

- Email: soporte@fullsys.com6. 🎉 Activa el tenant

- Documentación: Ver archivos `INSTALACION.md` y `PROYECTO_COMPLETADO.md`

#### Datos Requeridos para APIDIAN:

## 🙏 Agradecimientos

```json

- Laravel Team por el framework{

- Filament Team por el admin panel  "type_document_identification_id": 3,

- Stancl/Tenancy por la solución de multi-tenancy  "type_organization_id": 2,

- APIDIAN por la API de facturación electrónica  "type_regime_id": 2,

  "type_liability_id": 14,

---  "business_name": "NOMBRE EMPRESA",

  "merchant_registration": "0000000-00",

**Desarrollado con ❤️ usando Laravel 11 + Filament v3**  "municipality_id": 820,

  "address": "Dirección completa",
  "phone": "3001234567",
  "email": "empresa@example.com"
}
```

### WhatsApp (Evolution API)

Configuración en `.env`:

```env
EVOLUTION_API_BASE_URL=https://tu-api.evolution.com
EVOLUTION_API_KEY=tu_api_key
EVOLUTION_API_INSTANCE=nombre_instancia
```

## 📖 Uso del Sistema

### 1. Crear una Nueva Empresa (Tenant)

1. Accede al panel admin: `/admin`
2. Ve a **Empresas (Tenants)**
3. Click en **Crear**
4. Completa los siguientes tabs:
   - **Información General**: Datos básicos de la empresa
   - **Información Tributaria**: IDs de APIDIAN (tipo doc, régimen, etc.)
   - **Configuración APIDIAN**: Se configura automáticamente
   - **Configuración Email**: SMTP para envío de documentos
   - **WhatsApp**: Integración con Evolution API
   - **Plan y Suscripción**: Tipo de plan y fechas

5. Click en **Crear**
6. El sistema automáticamente:
   - Registra la empresa en APIDIAN
   - Crea la base de datos del tenant
   - Guarda el token de autenticación

### 2. Ver Estado de Configuración

En la lista de empresas puedes ver:
- ✅ **Logo** de la empresa
- 📋 **Nombre** y **NIT**
- 📧 **Email** de contacto
- 🏷️ **Estado**: Trial, Activo, Inactivo, Suspendido
- 💳 **Plan**: Trial, Básico, Profesional, Empresarial
- ✔️ **APIDIAN**: Configurado/No configurado
- 💬 **WhatsApp**: Habilitado/Deshabilitado

## 🔌 API - Integración APIDIAN

### Servicios Disponibles

```php
use App\Services\Apidian\ApidianService;

$apidian = new ApidianService();

// Configurar empresa
$response = $apidian->configureCompany($nit, $dv, $data);

// Enviar factura
$response = $apidian->sendInvoice($invoiceData);

// Enviar nota crédito
$response = $apidian->sendCreditNote($data);

// Enviar nota débito
$response = $apidian->sendDebitNote($data);

// Verificar estado
$response = $apidian->checkStatus($documentKey);

// Descargar PDF
$response = $apidian->downloadPdf($documentKey);

// Enviar nómina electrónica
$response = $apidian->sendPayroll($data);
```

### Respuesta Típica

```php
[
    'success' => true,
    'data' => [
        'token' => 'abc123...',
        'company' => [...],
        // más datos...
    ]
]
```

## 💬 WhatsApp - Envío de Facturas

```php
use App\Services\WhatsApp\EvolutionApiService;

$whatsapp = new EvolutionApiService();

// Enviar texto
$whatsapp->sendText('573001234567', 'Hola!');

// Enviar factura
$whatsapp->sendInvoice(
    '573001234567',
    'https://url-del-pdf.com/factura.pdf',
    'FACT-001'
);

// Verificar estado
$whatsapp->checkStatus();
```

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con coverage
php artisan test --coverage
```

## 🚀 Despliegue en Producción

### 1. Optimizar Aplicación

```bash
# Cache de configuración
php artisan config:cache

# Cache de rutas
php artisan route:cache

# Cache de vistas
php artisan view:cache

# Compilar assets para producción
npm run build
```

### 2. Configurar Variables de Entorno

```env
APP_ENV=production
APP_DEBUG=false
APIDIAN_ENVIRONMENT=production
```

### 3. Permisos de Almacenamiento

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 📚 Documentación Adicional

- [Laravel 11](https://laravel.com/docs/11.x)
- [Filament v3](https://filamentphp.com/docs/3.x)
- [Stancl Tenancy](https://tenancyforlaravel.com/docs/v3)
- [APIDIAN API](https://apidian.com/docs)

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es privado y propietario.

## 👥 Autores

- **Fullsys Team** - *Desarrollo inicial*

## 📞 Soporte

Para soporte, email: soporte@fullsys.com

---

**Desarrollado con ❤️ usando Laravel 11 + Filament v3**
