<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'CellPhone Store',
            'legal_name' => 'CellPhone Store S.A.S.',
            'nit_rut' => '900123456-7',
            'address' => 'Calle Principal #123',
            'city' => 'Bogotá',
            'state' => 'Cundinamarca',
            'country' => 'Colombia',
            'phone' => '6011234567',
            'email' => 'info@cellstore.com',
            'website' => 'www.cellstore.com',
            'currency' => 'COP',
            'currency_symbol' => '$',
            'tax_rate' => 19.00,
            'tax_name' => 'IVA',
            'invoice_footer' => 'Gracias por su compra. Garantía de 30 días en todos nuestros productos.',
            'terms_conditions' => 'Términos y condiciones de la empresa...',
            'is_active' => true,
        ]);
    }
}
