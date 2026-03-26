<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Productos Apple originales',
                'website' => 'https://www.apple.com',
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Productos Samsung originales',
                'website' => 'https://www.samsung.com',
            ],
            [
                'name' => 'Xiaomi',
                'slug' => 'xiaomi',
                'description' => 'Productos Xiaomi originales',
                'website' => 'https://www.mi.com',
            ],
            [
                'name' => 'Huawei',
                'slug' => 'huawei',
                'description' => 'Productos Huawei originales',
                'website' => 'https://www.huawei.com',
            ],
            [
                'name' => 'Motorola',
                'slug' => 'motorola',
                'description' => 'Productos Motorola originales',
                'website' => 'https://www.motorola.com',
            ],
            [
                'name' => 'OPPO',
                'slug' => 'oppo',
                'description' => 'Productos OPPO originales',
                'website' => 'https://www.oppo.com',
            ],
            [
                'name' => 'Vivo',
                'slug' => 'vivo',
                'description' => 'Productos Vivo originales',
                'website' => 'https://www.vivo.com',
            ],
            [
                'name' => 'Realme',
                'slug' => 'realme',
                'description' => 'Productos Realme originales',
                'website' => 'https://www.realme.com',
            ],
            [
                'name' => 'Nokia',
                'slug' => 'nokia',
                'description' => 'Productos Nokia originales',
                'website' => 'https://www.nokia.com',
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'Productos LG originales',
                'website' => 'https://www.lg.com',
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
