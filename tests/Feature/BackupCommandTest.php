<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class BackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_creates_zip_with_database_and_prunes_old_files(): void
    {
        $directory = sys_get_temp_dir().'/bc-backup-'.uniqid();
        $dbPath = sys_get_temp_dir().'/bc-backup-db-'.uniqid().'.sqlite';

        file_put_contents($dbPath, 'isi-database-uji');
        config()->set('database.connections.sqlite.database', $dbPath);

        File::ensureDirectoryExists($directory);

        foreach (range(1, 8) as $i) {
            file_put_contents($directory.'/backup-2020010'.$i.'-000000.zip', '');
        }

        $this->artisan('backup:run', ['--keep' => 7, '--dir' => $directory])->assertSuccessful();

        $zips = File::glob($directory.'/backup-*.zip');
        $this->assertCount(7, $zips);

        $newest = collect($zips)->sort()->last();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($newest) === true);
        $this->assertNotFalse($zip->locateName('database.sqlite'));
        $zip->close();

        foreach (File::glob($directory.'/backup-*.zip') as $file) {
            @unlink($file);
        }
        File::deleteDirectory($directory);
        @unlink($dbPath);
    }

    public function test_backup_fails_without_database_file(): void
    {
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('backup:run')->assertFailed();
    }

    public function test_backup_fails_when_database_file_is_missing(): void
    {
        config()->set('database.connections.sqlite.database', '/tmp/does-not-exist-bc.sqlite');

        $this->artisan('backup:run')->assertFailed();
    }
}
