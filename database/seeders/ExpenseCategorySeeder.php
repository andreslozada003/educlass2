<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Operativos',
                'slug' => 'operativos',
                'description' => 'Gastos de funcionamiento diario del local.',
                'expense_group' => 'operativos',
                'icon' => 'fas fa-store',
                'color' => '#2563eb',
                'children' => [
                    ['name' => 'Alquiler', 'slug' => 'operativos-alquiler', 'requires_approval' => true],
                    ['name' => 'Energia', 'slug' => 'operativos-energia'],
                    ['name' => 'Agua', 'slug' => 'operativos-agua'],
                    ['name' => 'Internet', 'slug' => 'operativos-internet'],
                    ['name' => 'Limpieza', 'slug' => 'operativos-limpieza'],
                    ['name' => 'Vigilancia', 'slug' => 'operativos-vigilancia', 'requires_approval' => true],
                    ['name' => 'Papeleria', 'slug' => 'operativos-papeleria'],
                ],
            ],
            [
                'name' => 'Personal',
                'slug' => 'personal',
                'description' => 'Pagos relacionados con colaboradores y nomina.',
                'expense_group' => 'personal',
                'icon' => 'fas fa-users',
                'color' => '#0f766e',
                'children' => [
                    ['name' => 'Sueldos', 'slug' => 'personal-sueldos', 'requires_approval' => true],
                    ['name' => 'Comisiones', 'slug' => 'personal-comisiones'],
                    ['name' => 'Bonificaciones', 'slug' => 'personal-bonificaciones'],
                    ['name' => 'Horas extra', 'slug' => 'personal-horas-extra'],
                    ['name' => 'Seguridad social', 'slug' => 'personal-seguridad-social', 'requires_approval' => true],
                ],
            ],
            [
                'name' => 'Taller tecnico',
                'slug' => 'taller-tecnico',
                'description' => 'Costos directos del taller y reparaciones.',
                'expense_group' => 'taller_tecnico',
                'icon' => 'fas fa-screwdriver-wrench',
                'color' => '#d97706',
                'children' => [
                    ['name' => 'Herramientas', 'slug' => 'taller-herramientas'],
                    ['name' => 'Consumibles de reparacion', 'slug' => 'taller-consumibles'],
                    ['name' => 'Mantenimiento de equipos', 'slug' => 'taller-mantenimiento'],
                    ['name' => 'Tercerizacion de reparaciones', 'slug' => 'taller-tercerizacion', 'requires_approval' => true],
                ],
            ],
            [
                'name' => 'Ventas y comercial',
                'slug' => 'ventas-comercial',
                'description' => 'Promocion, empaque y comercializacion.',
                'expense_group' => 'ventas_comercial',
                'icon' => 'fas fa-bullhorn',
                'color' => '#dc2626',
                'children' => [
                    ['name' => 'Publicidad', 'slug' => 'comercial-publicidad', 'requires_approval' => true],
                    ['name' => 'Redes sociales', 'slug' => 'comercial-redes-sociales'],
                    ['name' => 'Diseno', 'slug' => 'comercial-diseno'],
                    ['name' => 'Promociones', 'slug' => 'comercial-promociones'],
                    ['name' => 'Material POP', 'slug' => 'comercial-material-pop'],
                    ['name' => 'Empaques y bolsas', 'slug' => 'comercial-empaques-bolsas'],
                ],
            ],
            [
                'name' => 'Logistica',
                'slug' => 'logistica',
                'description' => 'Movilidad, envios y operacion externa.',
                'expense_group' => 'logistica',
                'icon' => 'fas fa-truck',
                'color' => '#0891b2',
                'children' => [
                    ['name' => 'Transporte', 'slug' => 'logistica-transporte'],
                    ['name' => 'Domicilios', 'slug' => 'logistica-domicilios'],
                    ['name' => 'Envios', 'slug' => 'logistica-envios'],
                    ['name' => 'Mensajeria', 'slug' => 'logistica-mensajeria'],
                ],
            ],
            [
                'name' => 'Administrativos',
                'slug' => 'administrativos',
                'description' => 'Servicios internos, software y cumplimiento.',
                'expense_group' => 'administrativos',
                'icon' => 'fas fa-file-invoice-dollar',
                'color' => '#7c3aed',
                'children' => [
                    ['name' => 'Software', 'slug' => 'administrativos-software'],
                    ['name' => 'Hosting', 'slug' => 'administrativos-hosting'],
                    ['name' => 'Dominios', 'slug' => 'administrativos-dominios'],
                    ['name' => 'Licencias', 'slug' => 'administrativos-licencias'],
                    ['name' => 'Honorarios contables', 'slug' => 'administrativos-honorarios-contables'],
                    ['name' => 'Impuestos', 'slug' => 'administrativos-impuestos', 'requires_approval' => true],
                    ['name' => 'Tramites', 'slug' => 'administrativos-tramites'],
                ],
            ],
            [
                'name' => 'Financieros',
                'slug' => 'financieros',
                'description' => 'Cargos bancarios e intereses.',
                'expense_group' => 'financieros',
                'icon' => 'fas fa-money-check-dollar',
                'color' => '#059669',
                'children' => [
                    ['name' => 'Comisiones bancarias', 'slug' => 'financieros-comisiones-bancarias'],
                    ['name' => 'Intereses', 'slug' => 'financieros-intereses'],
                    ['name' => 'Recargos', 'slug' => 'financieros-recargos'],
                ],
            ],
        ];

        foreach ($items as $item) {
            $children = $item['children'];
            unset($item['children']);

            $parent = ExpenseCategory::withTrashed()->updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, [
                    'requires_approval' => false,
                    'monthly_budget' => null,
                    'is_active' => true,
                ])
            );

            if ($parent->trashed()) {
                $parent->restore();
            }

            foreach ($children as $child) {
                $category = ExpenseCategory::withTrashed()->updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name' => $child['name'],
                        'description' => null,
                        'color' => $item['color'],
                        'icon' => $item['icon'],
                        'parent_id' => $parent->id,
                        'expense_group' => $item['expense_group'],
                        'requires_approval' => $child['requires_approval'] ?? false,
                        'monthly_budget' => null,
                        'is_active' => true,
                    ]
                );

                if ($category->trashed()) {
                    $category->restore();
                }
            }
        }
    }
}
