# Módulo de Notas Crédito y Débito - APIDIAN

## Descripción General

El módulo de **Notas Crédito y Débito** permite gestionar documentos electrónicos que modifican facturas ya emitidas, cumpliendo con los requisitos de la DIAN Colombia y la integración completa con APIDIAN.

## Características Principales

### Notas Crédito
- ✅ Referencia a factura original (Billing Reference)
- ✅ Códigos de discrepancia según DIAN
- ✅ Devolución parcial o total de bienes/servicios
- ✅ Descuentos y rebajas
- ✅ Anulación de facturas
- ✅ Cálculos automáticos de totales e impuestos
- ✅ Generación de CUDE (Código Único Nota Crédito)
- ✅ Integración completa con APIDIAN UBL 2.1
- ✅ Generación automática de PDF y XML
- ✅ Envío por email al cliente

### Notas Débito
- ✅ Referencia a factura original
- ✅ Códigos de discrepancia según DIAN
- ✅ Cobro de intereses
- ✅ Gastos de cobro
- ✅ Ajustes de valor
- ✅ Cálculos automáticos
- ✅ Generación de CUDE
- ✅ Integración APIDIAN
- ✅ PDF y XML automáticos

## Estructura de Base de Datos

### Tabla: credit_notes

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| invoice_id | bigint | Factura afectada |
| customer_id | bigint | Cliente |
| resolution_id | bigint | Resolución DIAN |
| prefix | varchar(10) | Prefijo (Ej: NC) |
| number | varchar(20) | Número consecutivo |
| type_document_id | int | 91 (Nota Crédito) |
| date | date | Fecha emisión |
| time | time | Hora emisión |
| billing_reference_number | varchar | Número factura original |
| billing_reference_uuid | varchar | CUFE factura original |
| billing_reference_issue_date | date | Fecha factura original |
| discrepancy_response_code | varchar(10) | Código motivo |
| discrepancy_response_description | text | Descripción motivo |
| line_extension_amount | decimal(15,2) | Subtotal |
| tax_exclusive_amount | decimal(15,2) | Base gravable |
| tax_inclusive_amount | decimal(15,2) | Total con IVA |
| payable_amount | decimal(15,2) | Total a pagar |
| cude | varchar(500) | Código Único Nota |
| qr_code | varchar(500) | Código QR |
| zip_key | varchar(500) | Clave ZIP |
| dian_status | enum | pending/approved/rejected |
| dian_response | json | Respuesta completa DIAN |
| pdf_url | varchar(500) | URL del PDF |
| xml_url | varchar(500) | URL del XML |
| status | enum | draft/sent/approved/rejected |
| sendmail | boolean | Enviar email |
| sendmailtome | boolean | Copiarme |

### Tabla: credit_note_items

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| credit_note_id | bigint | Nota crédito |
| product_id | bigint | Producto (opcional) |
| code | varchar(50) | Código producto |
| description | text | Descripción |
| type_item_identification_id | int | Tipo identificación (4=Estándar) |
| unit_measure_id | int | Unidad medida (70=Unidad) |
| invoiced_quantity | decimal(15,6) | Cantidad |
| price_amount | decimal(15,6) | Precio unitario |
| line_extension_amount | decimal(15,2) | Subtotal línea |
| discount_amount | decimal(15,2) | Descuento $ |
| discount_percent | decimal(5,2) | Descuento % |
| tax_id | int | ID impuesto (1=IVA) |
| tax_amount | decimal(15,2) | Valor IVA |
| taxable_amount | decimal(15,2) | Base gravable |
| tax_percent | decimal(5,2) | % IVA (0/5/19) |

**Nota:** La estructura de `debit_notes` y `debit_note_items` es idéntica, cambiando solo el `type_document_id` a 92.

## Códigos de Discrepancia DIAN

### Notas Crédito
| Código | Descripción |
|--------|-------------|
| 1 | Devolución parcial de bienes y/o servicios |
| 2 | Anulación de factura electrónica |
| 3 | Rebaja total aplicada |
| 4 | Descuento total aplicado |
| 5 | Rescisión: nulidad por falta de requisitos |
| 6 | Descuento parcial de bienes y/o servicios |
| 7 | Devolución total de bienes y/o servicios |

### Notas Débito
| Código | Descripción |
|--------|-------------|
| 1 | Intereses |
| 2 | Gastos por cobrar |
| 3 | Cambio del valor |
| 4 | Otros |

## Formulario Filament

### Secciones del Formulario

#### 1. Factura Original
- **Select Invoice**: Buscar y seleccionar factura a afectar
- Auto-completa: Cliente, Resolución, Número factura, CUFE, Fecha
- Validación: Solo facturas aprobadas

#### 2. Información General
- **Cliente**: Heredado de factura (disabled)
- **Resolución DIAN**: Específica para notas (type_document_id = 91 ó 92)
- **Prefijo y Número**: Auto-completado desde resolución
- **Fecha y Hora**: Fecha/hora actual por defecto

#### 3. Motivo de la Nota
- **Código de Motivo**: Select con códigos DIAN
- **Descripción del Motivo**: Textarea obligatorio

#### 4. Ítems
Repeater con campos:
- Producto (auto-completa código, descripción, precio, IVA)
- Código
- Descripción
- Cantidad
- Precio unitario
- Descuento %
- IVA %
- Subtotal (calculado)
- IVA (calculado)

#### 5. Totales
- Subtotal (auto-calculado)
- IVA (auto-calculado)
- Total (auto-calculado)

#### 6. Opciones de Envío
- Enviar email al cliente
- Enviarme copia

## Flujo de Trabajo

### Crear Nota Crédito

1. **Navegar** → Facturación Electrónica → Notas Crédito → Crear
2. **Seleccionar factura** original a afectar
3. Sistema auto-completa datos de la factura
4. **Seleccionar código** de motivo según DIAN
5. **Describir el motivo** detalladamente
6. **Agregar ítems** (productos/servicios a devolver o descontar)
7. Sistema **calcula totales** automáticamente
8. **Guardar** como borrador
9. **Revisar** y verificar datos
10. **Enviar a DIAN** desde vista de la nota

### Proceso de Envío APIDIAN

```
Nota Borrador → Botón "Enviar a DIAN" → Confirmación
    ↓
toApidianFormat() convierte a UBL 2.1
    ↓
POST /api/ubl2.1/credit-note (o debit-note)
    ↓
Respuesta DIAN:
  - CUDE generado
  - QR Code
  - PDF URL
  - XML URL
    ↓
Actualización registro:
  - status: sent → approved
  - dian_status: pending → approved
  - CUDE, QR, URLs guardados
    ↓
Notificación éxito al usuario
```

## Formato APIDIAN (UBL 2.1)

### Estructura JSON para Nota Crédito

```json
{
  "number": 1,
  "type_document_id": 91,
  "date": "2025-10-16",
  "time": "14:30:00",
  "resolution_number": "18760000001",
  "prefix": "NC",
  "notes": "Devolución parcial de productos por defecto de fabricación",
  "sendmail": true,
  "sendmailtome": false,
  
  "customer": {
    "identification_number": "900123456",
    "name": "EMPRESA XYZ S.A.S.",
    "phone": "3001234567",
    "address": "Calle 123 #45-67",
    "email": "facturacion@empresa.com",
    "merchant_registration": "900123456-1",
    "type_document_identification_id": 6,
    "type_organization_id": 2,
    "type_liability_id": 14,
    "type_regime_id": 48,
    "municipality_id": 11001
  },
  
  "billing_reference": {
    "number": "SETT991",
    "uuid": "27f5594d-e6e5-4e82-af2a-0ec285870a78c6eadff7afec45e3d01e4f01a7e3a",
    "issue_date": "2025-10-15"
  },
  
  "discrepancy_response": {
    "code": "1",
    "description": "Devolución parcial de productos por defecto de fabricación"
  },
  
  "legal_monetary_totals": {
    "line_extension_amount": "1000000.00",
    "tax_exclusive_amount": "1000000.00",
    "tax_inclusive_amount": "1190000.00",
    "payable_amount": "1190000.00"
  },
  
  "tax_totals": [
    {
      "tax_id": 1,
      "tax_amount": "190000.00",
      "taxable_amount": "1000000.00"
    }
  ],
  
  "credit_note_lines": [
    {
      "unit_measure_id": 70,
      "invoiced_quantity": "10.000000",
      "line_extension_amount": "1000000.00",
      "free_of_charge_indicator": false,
      "tax_totals": [
        {
          "tax_id": 1,
          "tax_amount": "190000.00",
          "taxable_amount": "1000000.00",
          "percent": "19.00"
        }
      ],
      "description": "Producto XYZ",
      "code": "PROD001",
      "type_item_identification_id": 4,
      "price_amount": "100000.000000",
      "base_quantity": "1.000000"
    }
  ]
}
```

**Nota:** La estructura para Nota Débito es idéntica, solo cambia:
- `type_document_id`: 92
- Endpoint: `/ubl2.1/debit-note`
- Campo de líneas: `debit_note_lines`

## Modelos Laravel

### CreditNote Model

#### Relaciones
- `invoice()` - BelongsTo Invoice
- `customer()` - BelongsTo Customer
- `resolution()` - BelongsTo Resolution
- `items()` - HasMany CreditNoteItem

#### Métodos Principales
- `calculateTotals()` - Recalcula subtotales, IVA y total
- `toApidianFormat()` - Convierte a formato UBL 2.1 para APIDIAN

#### Accessors
- `full_number` - Retorna prefix + number
- `is_approved` - Boolean si está aprobada
- `is_rejected` - Boolean si está rechazada
- `is_sent` - Boolean si fue enviada
- `is_draft` - Boolean si es borrador

### CreditNoteItem Model

#### Métodos
- `calculateTotals()` - Calcula subtotal, descuentos, IVA de la línea
- `populateFromProduct()` - Auto-completa desde producto

### DebitNote y DebitNoteItem

Funcionalidad idéntica a CreditNote/CreditNoteItem, adaptada para notas débito.

## Acciones Disponibles

### Tabla de Notas
- **Ver**: Ver detalles completos
- **Editar**: Solo si status = 'draft'
- **Eliminar**: Soft delete

### Vista de Nota
- **Editar**: Solo borradores
- **Enviar a DIAN**: Solo borradores, requiere confirmación
- **Descargar PDF**: Si pdf_url existe
- **Descargar XML**: Si xml_url existe

## Estados de la Nota

| Estado | Descripción |
|--------|-------------|
| draft | Borrador, editable |
| sent | Enviada a DIAN, esperando respuesta |
| approved | Aprobada por DIAN con CUDE |
| rejected | Rechazada por DIAN |

## Estados DIAN

| Estado | Descripción |
|--------|-------------|
| pending | Pendiente de envío |
| processing | En proceso en DIAN |
| approved | Aprobada con CUDE |
| rejected | Rechazada con errores |

## Validaciones

### Reglas de Negocio
1. Solo se pueden afectar facturas con estado 'approved'
2. La resolución debe ser específica para notas (type_document_id 91 ó 92)
3. La resolución debe estar vigente (date_from ≤ hoy ≤ date_to)
4. El código de discrepancia debe ser válido según DIAN
5. Debe tener al menos un ítem
6. Los totales deben cuadrar matemáticamente

### Validaciones APIDIAN
- Customer debe existir y tener datos completos
- Billing reference debe ser válida (factura existe y tiene CUFE)
- Legal monetary totals deben sumar correctamente
- Tax totals deben corresponder con líneas
- Cada línea debe tener descripción y código

## Integración con Sistema

### Navegación Filament
```
Panel Tenant → Facturación Electrónica
  ├── Facturas (sort: 1)
  ├── Notas Crédito (sort: 2)  ← heroicon-o-receipt-refund
  ├── Notas Débito (sort: 3)   ← heroicon-o-document-plus
  ├── Clientes (sort: 10)
  ├── Productos (sort: 11)
  ├── Categorías (sort: 12)
  └── Resoluciones (sort: 13)
```

### Permisos
- `view_credit_note`
- `view_any_credit_notes`
- `create_credit_note`
- `update_credit_note`
- `delete_credit_note`
- `send_credit_note_to_dian`

(Mismos permisos para debit_note)

## Notificaciones

### Éxito al Enviar
```
✓ Nota Crédito enviada exitosamente
CUDE: 27f5594d-e6e5-4e82-af2a-...
```

### Error al Enviar
```
✗ Error al enviar Nota Crédito
[Mensaje de error de APIDIAN]
```

## Archivos del Módulo

### Migraciones
- `2025_10_16_005500_create_credit_notes_table.php`
- `2025_10_16_005600_create_credit_note_items_table.php`
- `2025_10_16_005700_create_debit_notes_table.php`
- `2025_10_16_005800_create_debit_note_items_table.php`

### Modelos
- `app/Models/CreditNote.php`
- `app/Models/CreditNoteItem.php`
- `app/Models/DebitNote.php`
- `app/Models/DebitNoteItem.php`

### Enums
- `app/Enums/DiscrepancyCode.php` - Códigos DIAN

### Resources Filament
- `app/Filament/App/Resources/CreditNoteResource.php`
- `app/Filament/App/Resources/CreditNoteResource/Pages/CreateCreditNote.php`
- `app/Filament/App/Resources/CreditNoteResource/Pages/ViewCreditNote.php`
- `app/Filament/App/Resources/CreditNoteResource/Pages/ListCreditNotes.php`
- `app/Filament/App/Resources/CreditNoteResource/Pages/EditCreditNote.php`

- `app/Filament/App/Resources/DebitNoteResource.php`
- `app/Filament/App/Resources/DebitNoteResource/Pages/CreateDebitNote.php`
- `app/Filament/App/Resources/DebitNoteResource/Pages/ViewDebitNote.php`
- `app/Filament/App/Resources/DebitNoteResource/Pages/ListDebitNotes.php`
- `app/Filament/App/Resources/DebitNoteResource/Pages/EditDebitNote.php`

### Servicios
- `app/Services/Apidian/ApidianService.php` (métodos sendCreditNote() y sendDebitNote())

## Comandos Útiles

### Migrar base de datos
```bash
php artisan migrate
```

### Crear nota desde CLI (testing)
```bash
php artisan tinker

$invoice = App\Models\Invoice::first();
$note = App\Models\CreditNote::create([...]);
```

### Ver notas en cola
```bash
php artisan queue:work
```

## Troubleshooting

### Error: "No se puede afectar esta factura"
✓ Verificar que la factura esté aprobada (status = 'approved')

### Error: "Resolución no válida"
✓ Verificar que la resolución sea type_document_id = 91 (NC) ó 92 (ND)
✓ Verificar que la resolución esté vigente

### Error: "CUDE no generado"
✓ Revisar logs: `storage/logs/laravel.log`
✓ Verificar credenciales APIDIAN en `.env`
✓ Verificar formato UBL 2.1 con `toApidianFormat()`

### Error: "Totales no cuadran"
✓ Ejecutar `$note->calculateTotals()` manualmente
✓ Verificar cálculos en items

## Próximos Pasos

1. ✅ Módulo completo de Notas Crédito/Débito
2. ⏳ Módulo de Nómina Electrónica
3. ⏳ Módulo de Documentos Soporte
4. ⏳ Módulo de POS Electrónico

---

**Versión**: 1.0.0  
**Fecha**: 16 de octubre de 2025  
**Autor**: FullSys Santa Marta  
**Licencia**: Propietaria
