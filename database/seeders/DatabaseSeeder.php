<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            GallerySeeder::class,
        ]);
    }
}
