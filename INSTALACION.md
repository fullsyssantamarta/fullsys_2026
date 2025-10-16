# 🚀 Guía de Instalación Rápida - Fullsys

## ✅ ¡Ya está instalado!

El sistema ya está configurado con:
- ✅ Laravel 11
- ✅ Filament v3 Panel
- ✅ Multi-tenancy (stancl/tenancy)
- ✅ Integración APIDIAN
- ✅ Todos los modelos y servicios
- ✅ Migraciones ejecutadas

## 🎯 Próximos pasos para usar el sistema

### 1. Configurar la base de datos

Edita el archivo `.env` y configura tu base de datos:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fullsys_central
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 2. Configurar APIDIAN

Agrega tus credenciales de APIDIAN en `.env`:

```bash
APIDIAN_BASE_URL=https://api.apidian.com/api
APIDIAN_TOKEN=tu_token_de_apidian
APIDIAN_ENVIRONMENT=test
```

### 3. Crear usuario administrador

```bash
php artisan make:filament-user
```

Ingresa:
- **Name**: Tu nombre
- **Email**: admin@fullsys.com
- **Password**: (tu password segura)

### 4. Iniciar el servidor

```bash
php artisan serve
```

Accede a: **http://localhost:8000/admin**

---

## 📋 Panel de Administración

### Gestión de Empresas (Tenants)

Al crear una nueva empresa en el panel, el sistema automáticamente:

1. ✅ Valida los datos fiscales
2. 📡 Registra la empresa en APIDIAN  
3. 🔑 Obtiene y guarda el token de autenticación
4. 🗄️ Crea la base de datos del tenant
5. ✉️ Configura el correo (si se proporciona)
6. 💬 Configura WhatsApp (opcional)

### Campos requeridos para crear un Tenant:

#### Pestaña "Información General":
- Nombre Comercial *
- NIT *
- DV (Dígito de Verificación)
- Email *
- Teléfono
- Dirección
- Ciudad, Departamento, País

#### Pestaña "Información Tributaria":
Estos IDs corresponden a las tablas paramétricas de APIDIAN:

- **type_document_identification_id**: 3 (Cédula de Ciudadanía) o 6 (NIT)
- **type_organization_id**: 1 (Persona Jurídica) o 2 (Persona Natural)
- **type_regime_id**: 1 (Simplificado) o 2 (Común)
- **type_liability_id**: 14, 15, etc. (Responsabilidades fiscales)
- **municipality_id**: 820 (para Medellín), etc.
- **merchant_registration**: Matrícula mercantil

#### Pestaña "Configuración APIDIAN":
- Ambiente: test o production
- Token: *Se genera automáticamente*

#### Pestaña "Configuración Email" (Opcional):
- Servidor SMTP (ej: smtp.gmail.com)
- Puerto (587 o 465)
- Usuario y contraseña
- Encriptación (TLS/SSL)

#### Pestaña "WhatsApp" (Opcional):
- Habilitar WhatsApp
- Nombre de instancia Evolution API

#### Pestaña "Plan y Suscripción":
- Plan: trial, basic, professional, enterprise
- Fecha fin de prueba

---

## 🔧 Siguientes desarrollos

### Recursos de Filament a crear:

```bash
# Crear recursos para los módulos principales
php artisan make:filament-resource Customer --generate
php artisan make:filament-resource Product --generate
php artisan make:filament-resource Invoice --generate
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Resolution --generate
```

### Configurar Software y Resolución en APIDIAN

Después de crear un tenant, debes:

1. Configurar el software DIAN
2. Registrar las resoluciones de facturación
3. Configurar numeración

Estos pasos se pueden hacer mediante:
- Endpoints de APIDIAN
- Panel de administración (a desarrollar)

---

## 📚 Recursos

- **Panel Admin**: `/admin`
- **Documentación**: `README.md`
- **Colección Postman**: `apidian.json`
- **Config APIDIAN**: `config/apidian.php`
- **Servicio APIDIAN**: `app/Services/Apidian/ApidianService.php`

---

## 🐛 Troubleshooting

### Error de conexión a base de datos
```bash
# Verifica que MySQL esté corriendo
sudo service mysql status

# Verifica las credenciales en .env
cat .env | grep DB_
```

### Error en migraciones
```bash
# Limpiar y volver a migrar
php artisan migrate:fresh
```

### Error de permisos
```bash
# Dar permisos a storage
chmod -R 775 storage bootstrap/cache
```

### Limpiar cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## ✨ ¡Listo para usar!

Tu sistema de facturación electrónica está listo. 

**Siguiente paso**: Crear tu primera empresa (tenant) en el panel `/admin`
