<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('catalog:sync');
        Artisan::call('photos:relink');
    }
}
