<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Utilita baca & parse sel spreadsheet utk fitur impor Excel/CSV.
 * Dipakai OrderController (pesanan) dan controller modul operasional
 * (Data Jersey / Data Target) supaya aturan parsing seragam.
 */
class Spreadsheet
{
    /** Normalisasi judul kolom: huruf kecil, spasi tunggal, underscore jadi spasi. */
    public static function normalizeHeading(string $heading): string
    {
        $heading = str_replace(["\xEF\xBB\xBF", "\r", "\n", "\t", '_'], ' ', $heading);

        return preg_replace('/\s+/', ' ', mb_strtolower(trim($heading))) ?? '';
    }

    /**
     * Cocokkan baris judul file ke kunci kanonik. Lookup: judul dinormalisasi
     * lalu dicocokkan ke kunci kanonik itu sendiri atau daftar alias-nya —
     * sehingga file nyata dengan judul alternatif ("Nama Customer", "No HP",
     * "Deadline", …) tetap terbaca tanpa memakai template resmi. Urutan kolom
     * bebas, judul yang tak dikenal diabaikan, kolom kanonik pertama yang
     * cocok menang.
     *
     * @param  list<mixed>  $headers  sel baris judul (index numerik)
     * @param  array<string, list<string>>  $aliases  kunci kanonik (huruf asli) => alias
     * @return array<string, int> kunci kanonik ternormalisasi => index kolom
     */
    public static function mapHeadings(array $headers, array $aliases): array
    {
        $lookup = [];

        foreach ($aliases as $canonical => $aliasList) {
            $canon = self::normalizeHeading((string) $canonical);

            foreach (array_merge([(string) $canonical], $aliasList) as $variant) {
                $needle = self::normalizeHeading((string) $variant);

                if ($needle !== '' && ! isset($lookup[$needle])) {
                    $lookup[$needle] = $canon;
                }
            }
        }

        $map = [];

        foreach ($headers as $index => $header) {
            $key = self::normalizeHeading((string) $header);

            if ($key !== '' && isset($lookup[$key]) && ! isset($map[$lookup[$key]])) {
                $map[$lookup[$key]] = (int) $index;
            }
        }

        return $map;
    }

    /**
     * Parse tanggal sel. Diterima: Y-m-d, d/m/Y, d-m-Y, tanggal/bulan tanpa
     * tahun ("24/10" -> tahun berjalan), serta tanggal bulan campuran
     * "24-Oct", "24 Okt 2026", "24 Oktober" (nama bulan Indonesia /
     * Inggris 3 huruf; tanpa tahun -> tahun berjalan). Rollover tanggal
     * (31/02) ditolak; di luar rentang 1970..+2 tahun juga ditolak.
     */
    public static function parseDate(string $value): ?\DateTimeInterface
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // "24/10" / "24-10" (tanpa tahun) -> "24/10/[tahun berjalan]".
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})$/', $value, $dayMonth)) {
            $value = $dayMonth[1].'/'.$dayMonth[2].'/'.now()->year;
        }

        // "24-Oct" / "24 Okt 2026" -> "24 Oct [Y]" utk format !j M Y (M = bulan Inggris).
        if (preg_match('/^(\d{1,2})[\s.\-\/]([A-Za-z]+)\.?([\s.\-\/](\d{4}))?$/', $value, $matches)) {
            $monthMap = [
                'jan' => 'Jan', 'januari' => 'Jan', 'january' => 'Jan',
                'feb' => 'Feb', 'februari' => 'Feb', 'pebruari' => 'Feb', 'february' => 'Feb',
                'mar' => 'Mar', 'maret' => 'Mar', 'march' => 'Mar',
                'apr' => 'Apr', 'april' => 'Apr',
                'mei' => 'May', 'may' => 'May',
                'jun' => 'Jun', 'juni' => 'Jun', 'june' => 'Jun',
                'jul' => 'Jul', 'juli' => 'Jul', 'july' => 'Jul',
                'agu' => 'Aug', 'agt' => 'Aug', 'agustus' => 'Aug', 'agust' => 'Aug', 'aug' => 'Aug', 'august' => 'Aug',
                'sep' => 'Sep', 'sept' => 'Sep', 'september' => 'Sep',
                'okt' => 'Oct', 'oktober' => 'Oct', 'oct' => 'Oct', 'october' => 'Oct',
                'nov' => 'Nov', 'november' => 'Nov',
                'des' => 'Dec', 'desember' => 'Dec', 'dec' => 'Dec', 'december' => 'Dec',
            ];

            $month = $monthMap[mb_strtolower($matches[2])] ?? null;

            if ($month !== null) {
                $value = $matches[1].' '.$month.' '.(($matches[4] ?? '') !== '' ? $matches[4] : now()->year);
            }
        }

        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!j M Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }

            // createFromFormat tidak gagal untuk tanggal rollover (31/02/2026
            // menjadi 3 Maret) — cek warning bawaan DateTime.
            $errors = \DateTime::getLastErrors();

            if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }

            if ($date->format('Y') < '1970' || $date->format('Y') > (string) (now()->year + 2)) {
                continue;
            }

            return $date;
        }

        return null;
    }

    /**
     * Parse sel flag produksi: ok (setuju) / nok (belum) + turunan umum.
     *
     * @return array{0: bool, 1: string|null} [nilai, pesan error bila tidak dikenal]
     */
    public static function parseFlagOkNok(string $value): array
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return [false, null];
        }

        if (in_array($value, ['ok', '1', 'y', 'yes', 'ya', 'true', 'v'], true)) {
            return [true, null];
        }

        if (in_array($value, ['nok', '0', 'n', 'no', 'tidak', 'false', 'x'], true)) {
            return [false, null];
        }

        return [false, 'harus bernilai ok atau nok.'];
    }

    /**
     * Parse nominal uang dari sel spreadsheet. Hanya menerima angka polos atau
     * pemisah ribuan yang tegas (1.500.000 / 1,500,000) — desimal, rentang,
     * dan teks bebas ditolak agar tidak diam-diam salah hitung.
     */
    public static function parseAmount(string $value): ?int
    {
        $value = trim($value);

        if (preg_match('/^rp\s*/i', $value)) {
            $value = trim(substr($value, strlen('rp')));
        }

        if ($value === '' || ! preg_match('/^[0-9.,\s]{1,20}$/', $value)) {
            return null;
        }

        if (preg_match('/^\d{1,3}(?:[.,]\d{3})+$/', $value)) {
            $value = str_replace(['.', ',', ' '], '', $value);
        } elseif (preg_match('/^\d+$/', str_replace(' ', '', $value))) {
            $value = str_replace(' ', '', $value);
        } else {
            return null;
        }

        $amount = (int) $value;

        if ($amount > 100000000000) {
            return null;
        }

        return $amount;
    }

    public static function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_float($value)) {
            return $value == (int) $value ? (string) (int) $value : (string) $value;
        }

        return (string) $value;
    }

    /** Baca sheet pertama file XLSX menjadi baris sel string. */
    public static function readXlsxRows(string $path, int $maxRows): array
    {
        $reader = new XlsxReader;
        $reader->open($path);

        $rows = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = array_map(self::cellToString(...), $row->toArray());

                    if (count($rows) > $maxRows + 1) {
                        throw new \RuntimeException('File melebihi batas '.$maxRows.' baris data.');
                    }
                }

                break;
            }
        } finally {
            $reader->close();
        }

        return $rows;
    }

    /** Baca file CSV (delimiter , atau ; terdeteksi otomatis dari baris pertama). */
    public static function readCsvRows(string $path): array
    {
        $firstLine = strtok((string) file_get_contents($path), "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen($path, 'rb');
        $rows = [];

        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            // Baris kosong tetap disimpan agar nomor baris pada pesan error
            // (offset + 2) sama dengan nomor baris asli di file.
            $rows[] = array_map(self::cellToString(...), $cells);
        }

        fclose($handle);

        return $rows;
    }
}
