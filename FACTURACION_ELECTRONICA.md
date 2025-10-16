# 📄 Sistema de Facturación Electrónica - APIDIAN

## ✅ Completado - Módulo de Facturas

### 🎯 Funcionalidades Implementadas

#### 1. **Base de Datos**
- ✅ Tabla `invoices` con todos los campos APIDIAN
- ✅ Tabla `invoice_items` para líneas de factura
- ✅ Campos para CUFE, QR, PDF, XML, DIAN status
- ✅ Soporte para estados (draft, sent, approved, rejected, voided)

#### 2. **Modelos**
- ✅ `Invoice` con relaciones a Customer y Resolution
- ✅ `InvoiceItem` con relaciones a Invoice y Product
- ✅ Método `calculateTotals()` para cálculo automático de totales
- ✅ Método `toApidianFormat()` para conversión a formato APIDIAN
- ✅ Accesorios: `full_number`, `is_approved`, `is_rejected`, `is_sent`

#### 3. **Formulario de Factura (InvoiceResource)**

##### Sección: Datos de la Factura
- **Cliente**: Selector con búsqueda y opción de crear cliente rápido
- **Resolución DIAN**: Selector que auto-completa prefix y número
- **Fecha y Hora**: Campos de fecha/hora de emisión
- **Numeración**: Prefix + Number (auto-completado desde resolución)

##### Sección: Forma de Pago
- **Forma de Pago**: Contado (1) o Crédito (2)
- **Medio de Pago**: Efectivo, Transferencia, Tarjeta, etc.
- **Plazo**: Días de crédito (solo si es crédito)
- **Fecha de Vencimiento**: Auto-calculada según plazo

##### Sección: Productos y Servicios
- **Repeater dinámico** con:
  - Selector de producto (auto-completa código, descripción, precio, IVA)
  - Código del producto
  - Descripción
  - Cantidad
  - Precio Unitario
  - % Descuento
  - % IVA (0%, 5%, 19%)
  - **Subtotal** (calculado automáticamente)
  - **IVA** (calculado automáticamente)

##### Cálculos Automáticos
```
Subtotal = Cantidad × Precio
Descuento = Subtotal × (% Descuento / 100)
Base Gravable = Subtotal - Descuento
IVA = Base Gravable × (% IVA / 100)
Total Línea = Base Gravable + IVA
```

##### Resumen de Totales
- Subtotal general
- IVA total
- **Total a Pagar** (destacado en negrita)

##### Sección: Información Adicional
- Notas de la factura
- ☑ Enviar email al cliente
- ☑ Enviar copia a mi correo

#### 4. **Tabla de Facturas**

##### Columnas
- **Número**: Prefix + Number (ej: SETP990000001)
- **Cliente**: Nombre del cliente
- **Fecha**: Fecha de emisión
- **Total**: Monto total en COP
- **Estado**: Badge con color según estado
  - 🟤 Borrador
  - 🟡 Enviada
  - 🟢 Aprobada
  - 🔴 Rechazada
  - ⚪ Anulada
- **DIAN**: Icono ✓ si tiene CUFE, ✗ si no

##### Filtros
- Por Estado (draft, sent, approved, rejected, voided)
- Por Rango de Fechas (desde/hasta)

##### Acciones en Tabla
- **Ver**: Ver detalles de la factura
- **Editar**: Solo si está en borrador
- **Enviar a DIAN**: Envía la factura a APIDIAN
- **Descargar PDF**: Si ya fue enviada y tiene PDF
- **Descargar XML**: Si ya fue enviada y tiene XML

#### 5. **Vista de Factura (ViewInvoice)**

##### Acciones Principales

###### 📤 Enviar a DIAN
- Botón verde "Enviar a DIAN"
- Solo visible si status = 'draft'
- Confirmación antes de enviar
- Proceso:
  1. Calcula totales automáticamente
  2. Convierte a formato APIDIAN
  3. Llama a `ApidianService::sendInvoice()`
  4. Actualiza campos: cufe, qr_code, zip_key, dian_status, pdf_url, xml_url
  5. Cambia status a 'sent'
- Notificación de éxito/error

###### 📋 Eventos RADIAN
Grupo de acciones disponibles solo si `status = 'approved'`:

**030 - Acuse de Recibo** (ℹ️ Info)
- Confirma que se recibió la factura
- Botón azul con icono de check
- Envía evento a APIDIAN

**032 - Aceptación Expresa** (✅ Success)
- Acepta expresamente la factura
- Botón verde con icono de check-circle
- Confirma que la factura es correcta

**033 - Aceptación Tácita** (🕐 Success)
- Aceptación automática por vencimiento de plazo
- Botón verde con icono de reloj
- Se considera aceptada si no se rechaza en 3 días

**034 - Rechazo** (❌ Danger)
- Rechaza la factura con motivo
- Botón rojo con icono X
- Formulario modal solicita:
  - **Motivo del Rechazo** (textarea obligatorio)
- Envía evento con motivo a APIDIAN

**035 - Reclamo** (⚠️ Warning)
- Presenta un reclamo sobre la factura
- Botón naranja con icono de exclamación
- Formulario modal solicita:
  - **Motivo del Reclamo** (textarea obligatorio)
- Envía evento con motivo a APIDIAN

###### 📥 Descargas
- **Descargar PDF**: Abre PDF en nueva pestaña
- **Descargar XML**: Descarga archivo XML
- Solo visibles si existen las URLs

#### 6. **Integración APIDIAN**

##### Formato de Envío
El método `Invoice::toApidianFormat()` genera:
```json
{
  "number": 990000001,
  "type_document_id": 1,
  "date": "2025-10-15",
  "time": "14:30:00",
  "resolution_number": "18760000001",
  "prefix": "SETP",
  "customer": {
    "identification_number": "900428042",
    "name": "CLIENTE SAS",
    "email": "cliente@email.com",
    ...
  },
  "payment_form": {
    "payment_form_id": 1,
    "payment_method_id": 10,
    ...
  },
  "legal_monetary_totals": {
    "line_extension_amount": "1000.00",
    "tax_inclusive_amount": "1190.00",
    "payable_amount": "1190.00"
  },
  "tax_totals": [{
    "tax_id": 1,
    "tax_amount": "190.00",
    "percent": "19"
  }],
  "invoice_lines": [{
    "code": "PROD001",
    "description": "Producto de prueba",
    "invoiced_quantity": "1.00",
    "price_amount": "1000.00",
    "line_extension_amount": "1000.00",
    "tax_totals": [...]
  }]
}
```

##### Respuesta de APIDIAN
Se guarda en campos:
- `cufe`: Código Único de Factura Electrónica
- `qr_code`: URL del código QR
- `zip_key`: Clave para consultas asíncronas
- `dian_status`: Estado de la DIAN
- `dian_response`: Respuesta completa (JSON)
- `pdf_url`: URL del PDF generado
- `xml_url`: URL del XML firmado

##### Eventos RADIAN
Cada evento se envía con:
```json
{
  "event_code": "030",
  "invoice_number": "SETP990000001",
  "invoice_cufe": "abc123...",
  "description": "Motivo del evento",
  "date": "2025-10-15",
  "time": "14:30:00"
}
```

Los eventos se guardan en `dian_response['radian_events'][]`

### 📊 Flujo Completo de Facturación

```
1. Crear Factura (Borrador)
   ↓
2. Agregar Cliente + Productos
   ↓
3. Sistema calcula totales automáticamente
   ↓
4. Guardar como DRAFT
   ↓
5. Enviar a DIAN
   ↓
6. APIDIAN valida y retorna CUFE + PDF + XML
   ↓
7. Estado cambia a SENT
   ↓
8. DIAN aprueba → Estado = APPROVED
   ↓
9. Cliente puede enviar Eventos RADIAN:
   - Acuse Recibo (030)
   - Aceptación (032/033)
   - Rechazo (034)
   - Reclamo (035)
```

### 🎨 Características de UX

#### Formulario Dinámico
- ✅ Auto-completado de productos
- ✅ Cálculos en tiempo real
- ✅ Validación de campos
- ✅ Campos ocultos para APIDIAN
- ✅ Secciones colapsables

#### Tabla de Facturas
- ✅ Búsqueda por número/cliente
- ✅ Filtros por estado y fecha
- ✅ Ordenamiento por defecto: fecha DESC
- ✅ Acciones contextuales según estado
- ✅ Badges con colores semánticos

#### Vista de Factura
- ✅ Acciones agrupadas lógicamente
- ✅ Confirmaciones antes de acciones críticas
- ✅ Formularios modales para eventos RADIAN
- ✅ Notificaciones de éxito/error
- ✅ Visibilidad condicional de acciones

### 🔐 Seguridad

- ✅ Solo borradores son editables
- ✅ Confirmación antes de enviar a DIAN
- ✅ Eventos RADIAN solo en facturas aprobadas
- ✅ Validación de permisos (delete_invoices)

### 📱 Responsive

- ✅ Formulario adaptativo en móviles
- ✅ Tabla con scroll horizontal
- ✅ Acciones en menús desplegables
- ✅ Modales optimizados

### 🚀 Próximos Pasos

1. ✅ **Facturas Electrónicas** - COMPLETADO
2. ⏳ **Notas Crédito** - Pendiente
3. ⏳ **Notas Débito** - Pendiente
4. ⏳ **Nómina Electrónica** - Pendiente
5. ⏳ **Documento Soporte** - Pendiente

---

## 📖 Uso del Sistema

### Crear una Factura

1. Ve a **Facturación** → **Facturas Electrónicas**
2. Click en **Nueva Factura**
3. Selecciona el **Cliente** (o crea uno nuevo)
4. Selecciona la **Resolución DIAN**
5. Verifica fecha y hora
6. Configura **Forma de Pago**
7. Agrega **Productos**:
   - Selecciona producto
   - Ajusta cantidad
   - Verifica precio e IVA
   - El sistema calcula subtotales automáticamente
8. Revisa el **Resumen de Totales**
9. Agrega notas si es necesario
10. **Guardar**

### Enviar a DIAN

1. Abre la factura en modo Vista
2. Click en **Enviar a DIAN**
3. Confirma el envío
4. Espera la respuesta
5. Verifica el CUFE y estado
6. Descarga PDF/XML si lo necesitas

### Eventos RADIAN

1. Solo disponibles en facturas **Aprobadas**
2. Click en **Eventos RADIAN**
3. Selecciona el evento apropiado:
   - **Acuse**: Solo confirmas recibo
   - **Aceptar**: Estás de acuerdo con la factura
   - **Rechazar**: Indica el motivo del rechazo
   - **Reclamar**: Indica el motivo del reclamo
4. Confirma el evento

---

## 🛠️ Desarrollo Técnico

### Archivos Principales

```
app/
├── Models/
│   ├── Invoice.php                     # Modelo de factura
│   └── InvoiceItem.php                 # Modelo de línea de factura
│
├── Filament/App/Resources/
│   ├── InvoiceResource.php             # Resource principal
│   └── InvoiceResource/Pages/
│       ├── CreateInvoice.php           # Página de creación
│       ├── EditInvoice.php             # Página de edición
│       ├── ListInvoices.php            # Listado
│       └── ViewInvoice.php             # Vista con RADIAN
│
└── Services/Apidian/
    └── ApidianService.php              # Servicio de integración

database/migrations/
├── 2025_10_16_004420_create_invoices_table.php
└── 2025_10_16_004713_create_invoice_items_table.php
```

### Métodos Importantes

**Invoice Model**
- `calculateTotals()`: Suma totales de items
- `toApidianFormat()`: Convierte a formato APIDIAN
- `full_number`: Accessor para prefix + number

**InvoiceItem Model**
- `calculateTotals()`: Calcula subtotal, IVA
- `populateFromProduct()`: Llena desde producto

**ApidianService**
- `sendInvoice($data)`: Envía factura a DIAN
- `sendRadianEvent($data)`: Envía evento RADIAN

---

¡Sistema de Facturación Electrónica con APIDIAN completamente funcional! 🎉
