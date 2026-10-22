<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_refuses_production_environment(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DatabaseSeeder hanya untuk local/development');

        (new DatabaseSeeder)->run();
    }

    public function test_database_seeder_creates_demo_accounts_outside_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@banjarcustom.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'user@banjarcustom.test', 'role' => 'user']);
        $this->assertDatabaseHas('users', ['email' => 'produksi@banjarcustom.test', 'role' => 'produksi']);
    }
}
