<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class BackupRun extends Command
{
    protected $signature = 'backup:run {--keep=7 : Jumlah file backup yang disimpan} {--dir= : Directory tujuan (default storage/app/backups)}';

    protected $description = 'Backup database SQLite dan folder storage ke storage/app/backups (format zip)';

    public function handle(): int
    {
        $database = (string) config('database.connections.sqlite.database');

        if ($database === ':memory:' || $database === '') {
            $this->error('Database SQLite tidak ditemukan (pakai :memory: atau belum dikonfigurasi).');

            return self::FAILURE;
        }

        $isAbsolute = preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $database) === 1;
        $databasePath = $isAbsolute ? $database : base_path($database);

        if (! file_exists($databasePath)) {
            $this->error("File database tidak ditemukan: {$databasePath}");

            return self::FAILURE;
        }

        $directory = $this->option('dir') ?: storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $name = 'backup-'.now()->format('Ymd-His').'.zip';
        $path = $directory.DIRECTORY_SEPARATOR.$name;

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Gagal membuat file zip: {$path}");

            return self::FAILURE;
        }

        $zip->addFile($databasePath, 'database.sqlite');

        $storageRoot = storage_path('app/public');

        if (is_dir($storageRoot)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storageRoot, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($storageRoot) + 1));
                    $zip->addFile($file->getPathname(), 'storage/'.$relative);
                }
            }
        }

        $zip->close();

        $keep = max(1, (int) $this->option('keep'));
        $removed = $this->prune($directory, $keep);

        $this->info("Backup tersimpan: storage/app/backups/{$name} (hapus {$removed} lama, sisakan {$keep}).");

        return self::SUCCESS;
    }

    private function prune(string $directory, int $keep): int
    {
        $backups = collect(File::glob($directory.DIRECTORY_SEPARATOR.'backup-*.zip'))
            ->sort()
            ->reverse()
            ->values();

        $removed = 0;

        foreach ($backups->slice($keep) as $file) {
            if (@unlink($file)) {
                $removed++;
            }
        }

        return $removed;
    }
}
