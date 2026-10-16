<?php

namespace Tests\Concerns;

use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait InteractsWithXlsx
{
    /**
     * Baca seluruh baris dari respons unduhan XLSX (BinaryFileResponse).
     * Menerima TestResponse maupun respons dasarnya.
     *
     * @return list<list<string|null>>
     */
    protected function xlsxRows($response): array
    {
        $base = $response->baseResponse ?? $response;

        $this->assertInstanceOf(
            BinaryFileResponse::class,
            $base
        );

        $path = $base->getFile()->getPathname();

        $reader = new XlsxReader;
        $reader->open($path);

        $rows = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = array_map(
                        static fn ($value) => $value === null ? null : (string) $value,
                        $row->toArray()
                    );
                }

                break;
            }
        } finally {
            $reader->close();
        }

        return $rows;
    }

    /** Semua isi XLSX sebagai satu teks (untuk assertStringContains). */
    protected function xlsxText($response): string
    {
        return implode("\n", array_map(
            static fn (array $row) => implode('|', array_map(
                static fn ($value) => (string) ($value ?? ''),
                $row
            )),
            $this->xlsxRows($response)
        ));
    }
}
