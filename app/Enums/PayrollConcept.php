<?php

namespace App\Enums;

class PayrollConcept
{
    // Conceptos de Devengados (Accruals)
    public const ACCRUAL_CONCEPTS = [
        'SALARY' => 'Salario Básico',
        'TRANSPORT' => 'Auxilio de Transporte',
        'OVERTIME_DAY' => 'Horas Extras Diurnas',
        'OVERTIME_NIGHT' => 'Horas Extras Nocturnas',
        'OVERTIME_HOLIDAY' => 'Horas Extras Festivos',
        'BONUS' => 'Bonificaciones',
        'COMMISSION' => 'Comisiones',
        'SEVERANCE' => 'Cesantías',
        'INTEREST_SEVERANCE' => 'Intereses sobre Cesantías',
        'VACATION' => 'Vacaciones',
        'VACATION_BONUS' => 'Prima de Vacaciones',
        'SERVICE_BONUS' => 'Prima de Servicios',
        'FOOD_ALLOWANCE' => 'Auxilio de Alimentación',
        'HOUSING_ALLOWANCE' => 'Auxilio de Vivienda',
        'EDUCATION_ALLOWANCE' => 'Auxilio de Educación',
        'LAYOFF' => 'Indemnización',
        'MATERNITY' => 'Licencia de Maternidad',
        'PATERNITY' => 'Licencia de Paternidad',
        'SICK_LEAVE' => 'Incapacidad',
        'OTHER' => 'Otros Devengados',
    ];

    // Conceptos de Deducciones (Deductions)
    public const DEDUCTION_CONCEPTS = [
        'HEALTH' => 'Salud (EPS)',
        'PENSION' => 'Pensión',
        'UNEMPLOYMENT_FUND' => 'Fondo de Solidaridad Pensional',
        'TAX_WITHHOLDING' => 'Retención en la Fuente',
        'LOAN' => 'Préstamo',
        'ADVANCE' => 'Anticipo',
        'UNION_FEE' => 'Sindicato',
        'COOPERATIVE' => 'Cooperativa',
        'GARNISHMENT' => 'Embargo',
        'VOLUNTARY_PENSION' => 'Aporte Voluntario Pensión',
        'AFC' => 'AFC (Ahorro Voluntario Contractual)',
        'SAVINGS' => 'Ahorros',
        'DEBT' => 'Deuda',
        'OTHER' => 'Otras Deducciones',
    ];

    public static function getAccrualConcepts(): array
    {
        return self::ACCRUAL_CONCEPTS;
    }

    public static function getDeductionConcepts(): array
    {
        return self::DEDUCTION_CONCEPTS;
    }

    public static function getAllConcepts(): array
    {
        return array_merge(self::ACCRUAL_CONCEPTS, self::DEDUCTION_CONCEPTS);
    }
}
