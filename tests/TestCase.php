<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI tidak membangun aset Vite (public/build di-gitignore, job test tanpa npm build).
        // Tanpa ini, setiap render halaman melempar ViteManifestNotFoundException dan seluruh
        // test yang merender view gagal. Validasi build aset tetap di job "Vite build" CI.
        $this->withoutVite();
    }

    protected function setUpTraits(): void
    {
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($database !== ':memory:') {
            $this->fail(sprintf(
                'DB test harus ":memory:", dapat "%s". '
                    .'Kemungkinan config cache aktif — jalankan "composer test" (config:clear), '
                    .'bukan "php artisan test" saat config ter-cache.',
                $database
            ));
        }

        parent::setUpTraits();
    }
}
