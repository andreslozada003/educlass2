<?php

namespace App\Support;

class FacturacionCatalogos
{
    public static function regimenesFiscales(): array
    {
        return [
            '601' => 'General de Ley Personas Morales',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => 'Arrendamiento',
            '612' => 'Personas Fisicas con Actividades Empresariales y Profesionales',
            '616' => 'Sin obligaciones fiscales',
            '621' => 'Incorporacion Fiscal',
            '625' => 'Regimen de las Actividades Empresariales con ingresos a traves de Plataformas Tecnologicas',
            '626' => 'Regimen Simplificado de Confianza',
        ];
    }

    public static function usosCfdi(): array
    {
        return [
            'G01' => 'Adquisicion de mercancias',
            'G02' => 'Devoluciones, descuentos o bonificaciones',
            'G03' => 'Gastos en general',
            'I01' => 'Construcciones',
            'I02' => 'Mobiliario y equipo de oficina por inversiones',
            'I03' => 'Equipo de transporte',
            'I04' => 'Equipo de computo y accesorios',
            'I05' => 'Dados, troqueles, moldes, matrices y herramental',
            'I06' => 'Comunicaciones telefonicas',
            'I07' => 'Comunicaciones satelitales',
            'I08' => 'Otra maquinaria y equipo',
            'S01' => 'Sin efectos fiscales',
        ];
    }

    public static function formasPago(): array
    {
        return [
            '01' => 'Efectivo',
            '02' => 'Cheque nominativo',
            '03' => 'Transferencia electronica de fondos',
            '04' => 'Tarjeta de credito',
            '28' => 'Tarjeta de debito',
            '99' => 'Por definir',
        ];
    }

    public static function metodosPago(): array
    {
        return [
            'PUE' => 'Pago en una sola exhibicion',
            'PPD' => 'Pago en parcialidades o diferido',
        ];
    }

    public static function objetosImpuesto(): array
    {
        return [
            '01' => 'No objeto de impuesto',
            '02' => 'Si objeto de impuesto',
            '03' => 'Si objeto del impuesto y no obligado al desglose',
            '04' => 'Si objeto del impuesto y no causa impuesto',
        ];
    }

    public static function modosPac(): array
    {
        return [
            'sandbox' => 'Pruebas / Sandbox',
            'production' => 'Produccion',
        ];
    }

    public static function exportaciones(): array
    {
        return [
            '01' => 'No aplica',
            '02' => 'Definitiva',
            '03' => 'Temporal',
        ];
    }

    public static function label(array $items, ?string $key, string $default = 'No definido'): string
    {
        if ($key === null || $key === '') {
            return $default;
        }

        return $items[$key] ?? $default;
    }
}
