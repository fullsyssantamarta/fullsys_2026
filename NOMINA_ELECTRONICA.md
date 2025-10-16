# 📋 Módulo de Nómina Electrónica - Documentación Completa

## 📑 Tabla de Contenidos
1. [Introducción](#introducción)
2. [Estructura del Módulo](#estructura-del-módulo)
3. [Requisitos DIAN](#requisitos-dian)
4. [Modelos y Base de Datos](#modelos-y-base-de-datos)
5. [Recursos Filament](#recursos-filament)
6. [Integración APIDIAN](#integración-apidian)
7. [Flujo de Trabajo](#flujo-de-trabajo)
8. [Ejemplos de Uso](#ejemplos-de-uso)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 Introducción

El **Módulo de Nómina Electrónica** permite a las empresas colombianas generar, gestionar y enviar documentos electrónicos de nómina cumpliendo con los requisitos establecidos por la DIAN (Dirección de Impuestos y Aduanas Nacionales).

### Características Principales
- ✅ Gestión completa de trabajadores
- ✅ Generación de nóminas individuales
- ✅ Cálculos automáticos de devengados y deducciones
- ✅ Integración con APIDIAN para envío a DIAN
- ✅ Generación de CUNE (Código Único de Nómina Electrónica)
- ✅ PDFs y XMLs en formato UBL 2.1
- ✅ Cumplimiento normativo colombiano

---

## 🏗️ Estructura del Módulo

### Componentes Principales

```
app/
├── Enums/
│   ├── PayrollType.php         # Tipos de nómina, trabajadores, contratos
│   └── PayrollConcept.php      # Conceptos de devengados y deducciones
├── Models/
│   ├── Worker.php              # Modelo de trabajador
│   ├── Payroll.php             # Modelo de nómina
│   ├── PayrollAccrual.php      # Devengados detallados
│   └── PayrollDeduction.php    # Deducciones detalladas
├── Filament/App/Resources/
│   ├── WorkerResource.php      # Gestión de trabajadores
│   └── PayrollResource.php     # Gestión de nóminas
└── Services/Apidian/
    └── ApidianService.php      # Integración con APIDIAN
```

---

## 📜 Requisitos DIAN

### Documento Electrónico de Nómina

La DIAN exige que todas las empresas que contraten uno o más trabajadores generen y transmitan la **Nómina Electrónica** en formato **UBL 2.1**.

#### Elementos Obligatorios

1. **CUNE** - Código Único de Nómina Electrónica
2. **Información del empleador** (NIT, razón social)
3. **Información del trabajador** (identificación, nombres completos)
4. **Periodo de nómina** (fechas inicio y fin)
5. **Devengados** (salario, auxilio de transporte, horas extras, etc.)
6. **Deducciones** (salud, pensión, retención en la fuente, etc.)
7. **Neto a pagar**

#### Tipos de Nómina

- **Ordinaria (1)**: Nómina regular mensual
- **Extraordinaria (2)**: Primas, bonificaciones especiales

---

## 💾 Modelos y Base de Datos

### Tabla: `workers`

Almacena la información de los trabajadores/empleados.

```php
Schema::create('workers', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id')->index();
    $table->string('type_document_id', 2); // 13=CC, 22=CE, 31=NIT, etc.
    $table->string('identification_number', 20)->unique();
    $table->string('first_name', 100);
    $table->string('second_name', 100)->nullable();
    $table->string('surname', 100);
    $table->string('second_surname', 100)->nullable();
    $table->string('email', 100)->nullable();
    $table->string('phone', 20)->nullable();
    $table->string('address', 200)->nullable();
    $table->string('municipality_id', 5)->nullable(); // Código DANE
    $table->string('country_code', 2)->default('CO');
    
    // Información Laboral
    $table->string('type_worker_id', 2); // 01=Empleado, 02=Cooperativo, etc.
    $table->string('subtype_worker_id', 2)->default('00'); // 00=No aplica
    $table->string('type_contract_id', 1); // 1=Fijo, 2=Indefinido, etc.
    $table->boolean('high_risk_pension')->default(false);
    $table->boolean('integral_salary')->default(false);
    $table->decimal('salary', 15, 2)->default(0);
    
    // Información Bancaria
    $table->string('bank_name', 100)->nullable();
    $table->enum('account_type', ['savings', 'checking'])->nullable();
    $table->string('account_number', 50)->nullable();
    
    // Estado
    $table->enum('status', ['active', 'inactive', 'retired'])->default('active');
    $table->date('hire_date')->nullable();
    $table->date('retirement_date')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
});
```

#### Campos Clave

- **type_document_id**: Tipo de documento de identidad
  - `13` = Cédula de Ciudadanía
  - `22` = Cédula de Extranjería
  - `31` = NIT
  - `41` = Pasaporte
  
- **municipality_id**: Código DANE del municipio (5 dígitos)
  - Ejemplo: `47001` = Santa Marta
  
- **type_worker_id**: Tipo de trabajador según DIAN
  - `01` = Empleado
  - `02` = Trabajador Cooperativo
  - `03` = Aprendiz SENA
  - `04` = Estudiante en Práctica
  - `05` = Profesor
  
- **type_contract_id**: Tipo de contrato
  - `1` = Término Fijo
  - `2` = Término Indefinido
  - `3` = Obra o Labor
  - `4` = Aprendizaje
  - `5` = Prácticas

### Tabla: `payrolls`

Almacena las nóminas generadas.

```php
Schema::create('payrolls', function (Blueprint $table) {
    $table->id();
    $table->string('tenant_id')->index();
    $table->foreignId('worker_id')->constrained();
    $table->foreignId('resolution_id')->nullable()->constrained();
    
    // Numeración
    $table->string('prefix', 10)->default('NOM');
    $table->string('number', 20);
    $table->integer('consecutive');
    $table->string('type_document_id', 3)->default('102'); // 102 = Nómina Individual
    
    // Periodo
    $table->date('period_start_date');
    $table->date('period_end_date');
    $table->date('issue_date');
    $table->string('payroll_type_id', 1)->default('1'); // 1=Ordinaria
    $table->string('payment_method_id', 2)->default('42'); // 42=Transferencia
    $table->integer('worked_days')->default(30);
    $table->integer('worked_hours')->nullable();
    
    // Devengados
    $table->decimal('salary', 15, 2)->default(0);
    $table->decimal('transport_allowance', 15, 2)->default(0);
    $table->decimal('overtime', 15, 2)->default(0);
    $table->decimal('bonuses', 15, 2)->default(0);
    $table->decimal('commissions', 15, 2)->default(0);
    $table->decimal('severance', 15, 2)->default(0);
    $table->decimal('vacation', 15, 2)->default(0);
    $table->decimal('other_accruals', 15, 2)->default(0);
    $table->decimal('total_accruals', 15, 2)->default(0);
    
    // Deducciones
    $table->decimal('health_contribution', 15, 2)->default(0);
    $table->decimal('pension_contribution', 15, 2)->default(0);
    $table->decimal('unemployment_fund', 15, 2)->default(0);
    $table->decimal('tax_withholding', 15, 2)->default(0);
    $table->decimal('other_deductions', 15, 2)->default(0);
    $table->decimal('total_deductions', 15, 2)->default(0);
    
    // Neto a Pagar
    $table->decimal('net_payment', 15, 2)->default(0);
    
    // DIAN
    $table->string('cune', 100)->nullable()->unique();
    $table->text('qr_code')->nullable();
    $table->string('zip_key', 100)->nullable();
    $table->string('dian_status', 20)->default('draft');
    $table->json('dian_response')->nullable();
    $table->string('pdf_url')->nullable();
    $table->string('xml_url')->nullable();
    
    // Estado
    $table->enum('status', ['draft', 'sent', 'approved', 'rejected'])->default('draft');
    $table->boolean('sendmail')->default(true);
    
    $table->timestamps();
    $table->softDeletes();
});
```

### Tablas de Detalle

#### `payroll_accruals` - Detalle de Devengados
```php
Schema::create('payroll_accruals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
    $table->string('concept_code', 10); // SALARY, OVERTIME_DAY, etc.
    $table->string('concept_name', 100);
    $table->decimal('amount', 15, 2);
    $table->decimal('quantity', 10, 2)->nullable();
    $table->decimal('percentage', 5, 2)->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

#### `payroll_deductions` - Detalle de Deducciones
```php
Schema::create('payroll_deductions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
    $table->string('concept_code', 10); // HEALTH, PENSION, etc.
    $table->string('concept_name', 100);
    $table->decimal('amount', 15, 2);
    $table->decimal('percentage', 5, 2)->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

---

## 🎨 Recursos Filament

### WorkerResource

Gestión completa de trabajadores con 4 secciones:

#### 1. Información Personal
- Tipo de documento
- Número de identificación
- Nombres completos (4 partes según norma colombiana)

#### 2. Contacto
- Email
- Teléfono
- Dirección
- Municipio (código DANE)

#### 3. Información Laboral
- Tipo de trabajador
- Subtipo de trabajador
- Tipo de contrato
- Estado
- Fechas de contratación/retiro
- Pensión alto riesgo
- Salario integral

#### 4. Salario y Cuenta Bancaria
- Salario
- Nombre del banco
- Tipo de cuenta
- Número de cuenta

### PayrollResource

Generación y gestión de nóminas con 6 secciones:

#### 1. Trabajador y Periodo
- Selección de trabajador
- Tipo de nómina
- Método de pago
- Fechas del periodo
- Días trabajados

#### 2. Numeración
- Resolución DIAN
- Prefijo
- Número
- Consecutivo

#### 3. Devengados (con cálculo automático)
- Salario básico
- Auxilio de transporte
- Horas extras
- Bonificaciones
- Comisiones
- Cesantías
- Vacaciones
- Otros devengados
- **Total devengado** (calculado)

#### 4. Deducciones (con cálculo automático)
- Salud 4%
- Pensión 4%
- Fondo de solidaridad
- Retención en la fuente
- Otras deducciones
- **Total deducciones** (calculado)

#### 5. Resumen
- **NETO A PAGAR** (calculado automáticamente)

#### 6. Opciones
- Enviar email al trabajador

---

## 🔗 Integración APIDIAN

### Configuración

El servicio APIDIAN está configurado en `config/apidian.php`:

```php
return [
    'base_url' => env('APIDIAN_BASE_URL', 'https://api.apidian.com'),
    'token' => env('APIDIAN_TOKEN'),
    'timeout' => env('APIDIAN_TIMEOUT', 60),
    'connect_timeout' => env('APIDIAN_CONNECT_TIMEOUT', 30),
];
```

### Formato UBL 2.1

El modelo `Payroll` tiene el método `toApidianFormat()` que convierte la nómina al formato requerido:

```php
public function toApidianFormat(): array
{
    return [
        'sync' => true,
        'payroll_type_id' => $this->payroll_type_id,
        'consecutive' => $this->consecutive,
        'prefix' => $this->prefix,
        'notes' => $this->notes ?? '',
        'sendmail' => $this->sendmail,
        
        // Periodo
        'period' => [
            'admision_date' => $this->worker->hire_date?->format('Y-m-d'),
            'settlement_start_date' => $this->period_start_date->format('Y-m-d'),
            'settlement_end_date' => $this->period_end_date->format('Y-m-d'),
            'worked_time' => $this->worked_days,
            'issue_date' => $this->issue_date->format('Y-m-d'),
        ],
        
        // Trabajador
        'worker' => [
            'type_worker_id' => $this->worker->type_worker_id,
            'sub_type_worker_id' => $this->worker->subtype_worker_id,
            'payroll_type_document_identification_id' => $this->worker->type_document_id,
            'municipality_id' => $this->worker->municipality_id,
            'type_contract_id' => $this->worker->type_contract_id,
            'high_risk_pension' => $this->worker->high_risk_pension,
            'identification_number' => $this->worker->identification_number,
            'surname' => $this->worker->surname,
            'second_surname' => $this->worker->second_surname,
            'first_name' => $this->worker->first_name,
            'middle_name' => $this->worker->second_name,
            'address' => $this->worker->address,
            'integral_salarary' => $this->worker->integral_salary,
            'salary' => (float) $this->salary,
            'worker_code' => $this->worker->id,
        ],
        
        // Pago
        'payment' => [
            'payment_method_id' => $this->payment_method_id,
            'bank_name' => $this->worker->bank_name,
            'account_type' => $this->worker->account_type,
            'account_number' => $this->worker->account_number,
        ],
        
        // Devengados
        'accrued' => [
            'salary' => (float) $this->salary,
            'transportation_allowance' => (float) $this->transport_allowance,
            'overtime_surcharge' => (float) $this->overtime,
            'bonuses' => (float) $this->bonuses,
            'commissions' => (float) $this->commissions,
            'severance' => (float) $this->severance,
            'vacation' => (float) $this->vacation,
            'other_concepts' => (float) $this->other_accruals,
        ],
        
        // Deducciones
        'deductions' => [
            'health' => (float) $this->health_contribution,
            'pension' => (float) $this->pension_contribution,
            'solidarity_fund' => (float) $this->unemployment_fund,
            'withholding_source' => (float) $this->tax_withholding,
            'other_deductions' => (float) $this->other_deductions,
        ],
    ];
}
```

### Envío a DIAN

El método `sendPayroll` en `ApidianService`:

```php
public function sendPayroll(array $data)
{
    $response = $this->client()
        ->post("{$this->baseUrl}/ubl2.1/payroll", $data);
    
    if ($response->successful()) {
        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }
    
    return [
        'success' => false,
        'error' => $response->json(),
    ];
}
```

### Respuesta de APIDIAN

```json
{
  "cune": "abc123...",
  "QRStr": "data:image/png;base64,...",
  "zip_key": "xyz789...",
  "urlinvoicepdf": "https://apidian.com/pdf/...",
  "urlinvoicexml": "https://apidian.com/xml/..."
}
```

---

## 🔄 Flujo de Trabajo

### 1. Registrar Trabajadores

1. Ir a **Nómina Electrónica > Trabajadores**
2. Click en **Nuevo Trabajador**
3. Completar formulario (personal, contacto, laboral, salario)
4. Guardar

### 2. Crear Resolución DIAN

1. Ir a **Configuración > Resoluciones**
2. Crear resolución con `type_document_id = 102` (Nómina)
3. Definir rangos de numeración

### 3. Generar Nómina

1. Ir a **Nómina Electrónica > Nómina**
2. Click en **Nueva Nómina**
3. Seleccionar trabajador (auto-completa salario y auxilio de transporte)
4. Seleccionar periodo
5. Seleccionar resolución (auto-completa prefijo y número)
6. Ajustar devengados y deducciones
7. Verificar cálculo automático del neto a pagar
8. Guardar como borrador

### 4. Enviar a DIAN

1. Abrir la nómina creada
2. Verificar todos los datos
3. Click en **Enviar a DIAN**
4. El sistema:
   - Convierte al formato UBL 2.1
   - Envía a APIDIAN
   - Recibe CUNE
   - Actualiza estado
   - Guarda URLs de PDF y XML

### 5. Descargar Documentos

- Click en **Descargar PDF** para obtener el PDF firmado
- Click en **Descargar XML** para obtener el XML UBL 2.1

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Nómina Básica

**Trabajador:**
- Juan Pérez Gómez
- CC 12345678
- Salario: $1,500,000

**Devengados:**
- Salario básico: $1,500,000
- Auxilio de transporte: $162,000 (automático porque salario ≤ 2 SMMLV)

**Deducciones:**
- Salud 4%: $66,480
- Pensión 4%: $66,480

**Neto a Pagar:** $1,529,040

### Ejemplo 2: Nómina con Horas Extras

**Trabajador:**
- María López Díaz
- CC 87654321
- Salario: $2,000,000

**Devengados:**
- Salario básico: $2,000,000
- Auxilio de transporte: $162,000
- Horas extras: $250,000
- **Total:** $2,412,000

**Deducciones:**
- Salud 4%: $96,480
- Pensión 4%: $96,480
- **Total:** $192,960

**Neto a Pagar:** $2,219,040

### Ejemplo 3: Cálculo de Auxilio de Transporte

```php
// En el modelo Worker
public function calculateTransportAllowance(): float
{
    $smmlv = 1300000; // Salario Mínimo 2024
    $transportAllowance = 162000; // Auxilio 2024
    
    // Solo aplica si el salario es menor o igual a 2 SMMLV
    if ($this->salary <= ($smmlv * 2)) {
        return $transportAllowance;
    }
    
    return 0;
}
```

### Ejemplo 4: Cálculo Automático en Formulario

```php
// En PayrollResource
protected static function calculateTotals(Set $set, Get $get): void
{
    // Sumar todos los devengados
    $totalAccruals = 
        (float) ($get('salary') ?? 0) +
        (float) ($get('transport_allowance') ?? 0) +
        (float) ($get('overtime') ?? 0) +
        (float) ($get('bonuses') ?? 0) +
        // ... más conceptos
        
    // Sumar todas las deducciones
    $totalDeductions = 
        (float) ($get('health_contribution') ?? 0) +
        (float) ($get('pension_contribution') ?? 0) +
        // ... más conceptos
    
    // Calcular neto
    $netPayment = $totalAccruals - $totalDeductions;
    
    $set('total_accruals', $totalAccruals);
    $set('total_deductions', $totalDeductions);
    $set('net_payment', $netPayment);
}
```

---

## 🔧 Troubleshooting

### Error: "No se encuentra el trabajador"

**Causa:** El trabajador no existe o está inactivo.

**Solución:**
1. Verificar que el trabajador esté creado
2. Verificar que `status = 'active'`

### Error: "Resolución no encontrada"

**Causa:** No hay resoluciones de nómina activas.

**Solución:**
1. Crear resolución con `type_document_id = 102`
2. Verificar fechas de vigencia (`date_from` y `date_to`)

### Error: "CUNE ya existe"

**Causa:** Se está intentando enviar una nómina que ya fue enviada.

**Solución:**
- Las nóminas ya enviadas no se pueden editar
- Crear una nueva nómina o usar ajustes de nómina

### Error de APIDIAN: "Invalid worker data"

**Causa:** Datos del trabajador incompletos o inválidos.

**Solución:**
1. Verificar que el trabajador tenga:
   - Tipo de documento válido
   - Número de identificación
   - Nombres completos
   - Tipo de trabajador
   - Tipo de contrato
   - Municipio (código DANE)

### Error: "El total no cuadra"

**Causa:** Error en cálculos manuales.

**Solución:**
- Usar los campos calculados automáticamente
- Verificar que todos los montos sean numéricos
- El sistema recalcula al guardar con `calculateTotals()`

### Nómina no se envía por email

**Causa:** Toggle de email desactivado o email del trabajador vacío.

**Solución:**
1. Verificar que `sendmail = true` en la nómina
2. Verificar que el trabajador tenga email configurado
3. Verificar configuración de email en `.env`

---

## 📊 Conceptos DIAN

### Tipos de Devengados (PayrollConcept::ACCRUAL_CONCEPTS)

| Código | Concepto | Descripción |
|--------|----------|-------------|
| SALARY | Salario Básico | Salario base del trabajador |
| TRANSPORT | Auxilio de Transporte | Solo si salario ≤ 2 SMMLV |
| OVERTIME_DAY | Horas Extras Diurnas | Horas extras en horario diurno |
| OVERTIME_NIGHT | Horas Extras Nocturnas | Horas extras en horario nocturno |
| OVERTIME_HOLIDAY | Horas Extras Festivas | Horas extras en días festivos |
| BONUS | Bonificación | Bonificaciones habituales |
| COMMISSION | Comisión | Comisiones por ventas |
| SEVERANCE | Cesantías | Provisión de cesantías |
| VACATION | Vacaciones | Pago de vacaciones |
| SERVICE_BONUS | Prima de Servicios | Prima legal o extralegal |

### Tipos de Deducciones (PayrollConcept::DEDUCTION_CONCEPTS)

| Código | Concepto | Descripción |
|--------|----------|-------------|
| HEALTH | Salud | Aporte a EPS (4%) |
| PENSION | Pensión | Aporte a fondo de pensiones (4%) |
| UNEMPLOYMENT_FUND | Fondo de Solidaridad | 1% para salarios > 4 SMMLV |
| TAX_WITHHOLDING | Retención en la Fuente | Impuesto de renta retenido |
| LOAN | Préstamo | Descuento por préstamo |
| UNION_FEE | Cuota Sindical | Aporte al sindicato |
| DEBT | Libranza | Descuentos por libranzas |

---

## 📝 Notas Importantes

1. **CUNE vs CUFE**
   - **CUNE**: Código Único de Nómina Electrónica (nómina)
   - **CUFE**: Código Único de Factura Electrónica (facturas)

2. **Auxilio de Transporte 2024**
   - Monto: $162,000
   - Solo aplica para salarios ≤ $2,600,000 (2 SMMLV)

3. **Aportes a Seguridad Social**
   - Salud: 12.5% total (8.5% empleador + 4% empleado)
   - Pensión: 16% total (12% empleador + 4% empleado)
   - ARL: Variable según nivel de riesgo (empleador)

4. **Documentos Soportados**
   - `102`: Nómina Individual
   - `103`: Nómina Individual de Ajuste

5. **Estados de Nómina**
   - `draft`: Borrador (editable)
   - `sent`: Enviada a DIAN
   - `approved`: Aprobada por DIAN
   - `rejected`: Rechazada por DIAN

---

## 🚀 Próximas Mejoras

- [ ] Nóminas masivas (múltiples trabajadores)
- [ ] Ajustes de nómina (documento 103)
- [ ] Notas de ajuste de nómina
- [ ] Reportes de nómina
- [ ] Exportación a Excel
- [ ] Integración con módulo de contabilidad
- [ ] Provisiones automáticas (cesantías, vacaciones, primas)
- [ ] Cálculo automático de liquidaciones

---

## 📞 Soporte

Para más información sobre la implementación o problemas técnicos:
- Revisar logs en `storage/logs/laravel.log`
- Documentación APIDIAN: https://apidian.com/docs
- Documentación DIAN sobre nómina electrónica

---

**Última actualización:** Enero 2025  
**Versión del módulo:** 1.0.0  
**Compatibilidad:** Laravel 11 + Filament v3
