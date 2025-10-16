<?php

namespace App\Enums;

class PayrollType
{
    // Tipos de Nómina según DIAN
    public const TYPES = [
        '1' => 'Nómina Ordinaria',
        '2' => 'Nómina Extraordinaria',
    ];

    // Tipos de Trabajador
    public const WORKER_TYPES = [
        '01' => 'Empleado',
        '02' => 'Trabajador Asociado Cooperativo',
        '03' => 'Aprendiz',
        '04' => 'Estudiante',
        '05' => 'Profesor',
    ];

    // Subtipos de Trabajador
    public const WORKER_SUBTYPES = [
        '00' => 'No aplica',
        '01' => 'Dependiente pensionado activo',
        '02' => 'Dependiente pensionado inactivo',
        '03' => 'Dependiente y de entidad pública del régimen especial o propio',
    ];

    // Tipos de Contrato
    public const CONTRACT_TYPES = [
        '1' => 'Término fijo',
        '2' => 'Término indefinido',
        '3' => 'Obra o labor',
        '4' => 'Aprendizaje',
        '5' => 'Prácticas o pasantías',
    ];

    // Métodos de Pago
    public const PAYMENT_METHODS = [
        '1' => 'Contado',
        '2' => 'Crédito',
        '10' => 'Consignación bancaria',
        '42' => 'Transferencia bancaria',
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }

    public static function getWorkerTypes(): array
    {
        return self::WORKER_TYPES;
    }

    public static function getWorkerSubtypes(): array
    {
        return self::WORKER_SUBTYPES;
    }

    public static function getContractTypes(): array
    {
        return self::CONTRACT_TYPES;
    }

    public static function getPaymentMethods(): array
    {
        return self::PAYMENT_METHODS;
    }
}
