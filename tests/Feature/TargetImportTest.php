<?php

namespace Tests\Feature;

use App\Models\MasterOption;
use App\Models\TargetData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class TargetImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function produksi(): User
    {
        return User::factory()->create(['role' => 'produksi']);
    }

    private function makeFile(string $content, string $filename): UploadedFile
    {
        $path = sys_get_temp_dir().'/bc-target-import-'.uniqid('', true).'-'.$filename;

        file_put_contents($path, $content);

        $mime = str_ends_with($filename, '.csv')
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return new UploadedFile($path, $filename, $mime, null, true);
    }

    private function makeXlsx(array $rows, string $name = 'target.xlsx'): UploadedFile
    {
        $path = sys_get_temp_dir().'/bc-target-import-'.uniqid('', true).'.xlsx';

        $writer = new XlsxWriter;
        $writer->openToFile($path);

        foreach ($rows as $cells) {
            $writer->addRow(new Row(array_map(
                static fn ($value) => Cell::fromValue($value),
                $cells,
            )));
        }

        $writer->close();

        return new UploadedFile(
            $path,
            $name,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    public function test_guest_redirect_and_produksi_forbidden(): void
    {
        $this->get(route('admin.target.import'))->assertRedirect(route('login'));
        $this->post(route('admin.target.import.store'))->assertRedirect(route('login'));

        $produksi = $this->produksi();

        $this->actingAs($produksi)->get(route('admin.target.import'))->assertForbidden();
        $this->actingAs($produksi)->post(route('admin.target.import.store'))->assertForbidden();
    }

    public function test_import_form_shows_alias_column_guide_without_template_download(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.target.import'))
            ->assertOk()
            ->assertSee('Unggah File')
            ->assertSee('Kolom yang Dikenali')
            ->assertSee('Pelanggan')
            ->assertSee('template resmi tidak wajib', false)
            ->assertSee('OK')
            ->assertSee('NOK');
    }

    public function test_alias_headings_are_matched_and_flags_and_total_parse(): void
    {
        // File nyata: judul alternatif, urutan diacak, kolom NO diabaikan.
        $rows = [
            ['NO', 'Tgl Ambil', 'Produksi', 'Pelanggan', 'Jenis Produk', 'Finishing', 'Warna', 'Jumlah', 'Type', 'Material', 'Pesan Bahan'],
            ['1', '24-Oct', 'OK', 'ARBI PAIJO - 1810', 'Jersey', 'NOK', 'Navy', '24', 'Pendek', 'Milano', 'ya'],
        ];

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeXlsx($rows)]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 1 data')
            ->assertSee('Master data baru dibuat')
            ->assertSee('diabaikan');

        $target = TargetData::firstOrFail();

        $this->assertSame('2026-10-24', $target->waktu_pengambilan->format('Y-m-d'));
        $this->assertSame(TargetData::OK, $target->progres_produksi);
        $this->assertSame(TargetData::NOK, $target->finish);
        $this->assertSame(TargetData::OK, $target->pesanan_bahan);
        $this->assertSame('ARBI PAIJO - 1810', $target->customer);
        $this->assertSame('Jersey', $target->jenis);
        $this->assertSame('Navy', $target->warna);
        $this->assertSame(24, $target->total);
        $this->assertSame('Pendek', $target->type);
        $this->assertSame('Milano', $target->bahan);

        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_TARGET_LINE, 'name' => 'Jersey']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_COLOR, 'name' => 'Navy']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_TARGET_TYPE, 'name' => 'Pendek']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_MATERIAL, 'name' => 'Milano']);
    }

    public function test_blank_total_falls_back_to_zero_with_warning(): void
    {
        $csv = implode("\n", [
            'customer,total',
            'Total Kosong,',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'total-kosong.csv')]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 1 data')
            ->assertSee('1 baris tanpa TOTAL diisi 0');

        $this->assertSame(0, TargetData::firstOrFail()->total);
    }

    public function test_bad_flag_and_bad_total_block_import(): void
    {
        $admin = $this->admin();

        $badFlag = implode("\n", [
            'customer,pesanan bahan',
            'Flag Salah,mungkin',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($badFlag, 'flag.csv')])
            ->assertOk()
            ->assertSee('harus bernilai ok atau nok');

        $this->assertDatabaseCount('target_data', 0);

        $badTotal = implode("\n", [
            'customer,total',
            'Total Rusak,-5',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($badTotal, 'total.csv')])
            ->assertOk()
            ->assertSee('TOTAL tidak valid');

        $this->assertDatabaseCount('target_data', 0);
    }

    public function test_invalid_row_blocks_entire_import_transaktional(): void
    {
        $csv = implode("\n", [
            'customer',
            'Pelanggan Valid',
            'Pelanggan Panjang '.str_repeat('x', 160),
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'invalid.csv')]);

        $response->assertOk()
            ->assertSee('Impor dibatalkan')
            ->assertSee('Baris 3');

        $this->assertDatabaseCount('target_data', 0);
    }

    public function test_missing_customer_is_fatal(): void
    {
        $csv = "jenis\nJersey\n";

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'kurang.csv')])
            ->assertOk()
            ->assertSee('Kolom wajib tidak ditemukan')
            ->assertSee('customer');

        $this->assertDatabaseCount('target_data', 0);
    }

    public function test_existing_master_not_duplicated_on_import(): void
    {
        MasterOption::create(['kind' => MasterOption::KIND_MATERIAL, 'name' => 'Milano']);

        $csv = implode("\n", [
            'customer,bahan',
            'Master Duplikat,milano',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'duplikat.csv')]);

        $response->assertOk()->assertSee('Berhasil mengimpor 1 data');

        $this->assertSame(1, MasterOption::where('kind', MasterOption::KIND_MATERIAL)->count());
        $this->assertDatabaseMissing('options', ['kind' => MasterOption::KIND_MATERIAL, 'name' => 'milano']);
    }

    public function test_blank_lines_skipped_and_bad_date_names_real_line(): void
    {
        $csv = implode("\n", [
            'customer,waktu pengambilan',
            'Baik,20/10/2026',
            '',
            'Tanggal Rusak,31/02/2026',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'baris.csv')])
            ->assertOk()
            ->assertSee('Baris 4')
            ->assertSee('Waktu pengambilan tidak valid');

        $this->assertDatabaseCount('target_data', 0);
    }

    public function test_csv_import_with_pure_canonical_headers_and_all_flags(): void
    {
        $csv = implode("\n", [
            'customer,jenis,waktu pengambilan,type,bahan,warna,pesanan bahan,progres produksi,finish,total',
            'Siti Aminah,Kaos,01/11/2026,Lengan Panjang,Cotton,Navy,NOK,OK,OK,"1.500.000"',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.target.import.store'), ['file' => $this->makeFile($csv, 'kanonik.csv')])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 data');

        $target = TargetData::firstOrFail();

        $this->assertSame('2026-11-01', $target->waktu_pengambilan->format('Y-m-d'));
        $this->assertSame(TargetData::NOK, $target->pesanan_bahan);
        $this->assertSame(TargetData::OK, $target->progres_produksi);
        $this->assertSame(TargetData::OK, $target->finish);
        $this->assertSame(1500000, $target->total);
    }
}
