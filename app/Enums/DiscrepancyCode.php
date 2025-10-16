<?php

namespace App\Enums;

class DiscrepancyCode
{
    // Códigos de Discrepancia para Notas Crédito según DIAN
    public const CREDIT_NOTE_CODES = [
        '1' => 'Devolución parcial de bienes y/o servicios',
        '2' => 'Anulación de factura electrónica',
        '3' => 'Rebaja total aplicada',
        '4' => 'Descuento total aplicado',
        '5' => 'Rescisión: nulidad por falta de requisitos',
        '6' => 'Descuento parcial de bienes y/o servicios',
        '7' => 'Devolución total de bienes y/o servicios',
    ];

    // Códigos de Discrepancia para Notas Débito según DIAN
    public const DEBIT_NOTE_CODES = [
        '1' => 'Intereses',
        '2' => 'Gastos por cobrar',
        '3' => 'Cambio del valor',
        '4' => 'Otros',
    ];

    public static function getCreditNoteCodes(): array
    {
        return self::CREDIT_NOTE_CODES;
    }

    public static function getDebitNoteCodes(): array
    {
        return self::DEBIT_NOTE_CODES;
    }
}
