<?php

namespace App\Support;

class GastosCatalogos
{
    public static function metodosPago(): array
    {
        return [
            'cash' => 'Efectivo',
            'card' => 'Tarjeta',
            'transfer' => 'Transferencia',
            'check' => 'Cheque',
            'other' => 'Otro',
        ];
    }

    public static function fuentesPago(): array
    {
        return [
            'caja_general' => 'Caja general',
            'banco' => 'Banco',
            'transferencia' => 'Transferencia',
            'nequi_daviplata' => 'Nequi / Daviplata',
            'tarjeta' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            'credito_proveedor' => 'Credito proveedor',
        ];
    }

    public static function estadosPago(): array
    {
        return [
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'partial' => 'Parcial',
            'cancelled' => 'Anulado',
            'overdue' => 'Vencido',
        ];
    }

    public static function estadosAprobacion(): array
    {
        return [
            'not_required' => 'No requerida',
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
        ];
    }

    public static function tiposGasto(): array
    {
        return [
            'fixed' => 'Fijo',
            'variable' => 'Variable',
        ];
    }

    public static function periodosRecurrentes(): array
    {
        return [
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            'yearly' => 'Anual',
        ];
    }

    public static function gruposCategorias(): array
    {
        return [
            'operativos' => 'Operativos',
            'personal' => 'Personal',
            'taller_tecnico' => 'Taller tecnico',
            'ventas_comercial' => 'Ventas y comercial',
            'logistica' => 'Logistica',
            'administrativos' => 'Administrativos',
            'financieros' => 'Financieros',
        ];
    }

    public static function coloresCategoria(): array
    {
        return [
            '#2563eb',
            '#0f766e',
            '#d97706',
            '#dc2626',
            '#7c3aed',
            '#4f46e5',
            '#059669',
            '#0891b2',
        ];
    }

    public static function iconosCategoria(): array
    {
        return [
            'fas fa-store',
            'fas fa-bolt',
            'fas fa-wifi',
            'fas fa-users',
            'fas fa-screwdriver-wrench',
            'fas fa-bullhorn',
            'fas fa-truck',
            'fas fa-file-invoice-dollar',
            'fas fa-money-check-dollar',
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
