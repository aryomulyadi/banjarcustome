<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Sablon', 'description' => 'Jasa sablon kaos dan apparel dengan teknik sablon awet, tidak mudah pecah.'],
            ['name' => 'Kaos Polos', 'description' => 'Kaos polos cotton combed siap sablon, tersedia ecer dan grosir.'],
            ['name' => 'Jersey Printing', 'description' => 'Jersey printing full color untuk futsal, basket, voli, dan tim olahraga.'],
            ['name' => 'Kemeja', 'description' => 'Kemeja kerja, seragam kantor, dan kemeja custom sesuai kebutuhan.'],
            ['name' => 'Jaket', 'description' => 'Jaket hoodie, coach jacket, dan jaket custom komunitas.'],
            ['name' => 'Merchandise', 'description' => 'Totebag, topi, dan merchandise custom untuk event dan promosi.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category
            );
        }
    }
}
