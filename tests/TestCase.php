<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
