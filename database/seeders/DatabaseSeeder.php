<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'DatabaseSeeder hanya untuk local/development — jangan jalankan db:seed di produksi. '
                .'Buat akun admin via CLI: php artisan tinker --execute="App\Models\User::create([...])".'
            );
        }

        User::firstOrCreate(
            ['email' => 'admin@banjarcustom.test'],
            [
                'name' => 'Admin Banjar Custome',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@banjarcustom.test'],
            [
                'name' => 'Pelanggan Demo',
                'password' => 'password',
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'produksi@banjarcustom.test'],
            [
                'name' => 'Operator Produksi',
                'password' => 'password',
                'role' => User::ROLE_PRODUKSI,
            ]
        );

        $this->call([
            GallerySeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            JerseyOptionSeeder::class,
            OperationalOptionSeeder::class,
        ]);

        Artisan::call('catalog:sync');
    }
}
