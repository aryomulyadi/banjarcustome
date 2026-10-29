<?php

namespace Tests\Feature;

use App\Models\JerseyData;
use App\Models\MasterOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class JerseyImportTest extends TestCase
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
        $path = sys_get_temp_dir().'/bc-jersey-import-'.uniqid('', true).'-'.$filename;

        file_put_contents($path, $content);

        $mime = str_ends_with($filename, '.csv')
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return new UploadedFile($path, $filename, $mime, null, true);
    }

    private function makeXlsx(array $rows, string $name = 'jersey.xlsx'): UploadedFile
    {
        $path = sys_get_temp_dir().'/bc-jersey-import-'.uniqid('', true).'.xlsx';

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
        $this->get(route('admin.jersey.import'))->assertRedirect(route('login'));
        $this->post(route('admin.jersey.import.store'))->assertRedirect(route('login'));

        $produksi = $this->produksi();

        $this->actingAs($produksi)->get(route('admin.jersey.import'))->assertForbidden();
        $this->actingAs($produksi)->post(route('admin.jersey.import.store'))->assertForbidden();
    }

    public function test_import_form_shows_alias_column_guide_without_template_download(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.jersey.import'))
            ->assertOk()
            ->assertSee('Unggah File')
            ->assertSee('Kolom yang Dikenali')
            ->assertSee('tgl masuk')
            ->assertSee('template resmi tidak wajib', false)
            ->assertSee('NO')
            ->assertSee('SISA HARI');
    }

    public function test_alias_headings_are_matched_and_derived_columns_ignored(): void
    {
        // File nyata: judul alternatif, kolom turunan NO & SISA HARI, urutan diacak.
        $rows = [
            ['SISA HARI', 'Nama/No. Customer', 'Deadline', 'Status Jersey', 'Designer', 'NO', 'TGL MASUK', 'Kerah', 'Bahan', 'Model Jersey', 'Catatan'],
            ['13', 'ARBI PAIJO - 1810', '24-Oct', 'Printing', 'Budi', '1', '20/10/2026', 'O-Neck', 'Milano', 'Pendek', 'Menunggu DP'],
        ];

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeXlsx($rows)]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 1 data')
            ->assertSee('Master data baru dibuat')
            ->assertSee('diabaikan');

        $entry = JerseyData::firstOrFail();

        $this->assertSame('2026-10-20', $entry->tanggal_masuk->format('Y-m-d'));
        $this->assertSame('2026-10-24', $entry->deadline->format('Y-m-d'));
        $this->assertSame('Printing', $entry->status);
        $this->assertSame('Budi', $entry->desainer);
        $this->assertSame('O-Neck', $entry->jenis_kerah);
        $this->assertSame('Milano', $entry->jenis_bahan);
        $this->assertSame('Pendek', $entry->model);
        $this->assertSame('Menunggu DP', $entry->catatan);
        $this->assertSame('ARBI PAIJO - 1810', $entry->customer);

        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_DESIGNER, 'name' => 'Budi']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_COLLAR, 'name' => 'O-Neck']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_FABRIC, 'name' => 'Milano']);
        $this->assertDatabaseHas('options', ['kind' => MasterOption::KIND_JERSEY_MODEL, 'name' => 'Pendek']);
    }

    public function test_status_is_strict_to_known_values_and_blank_defaults(): void
    {
        $admin = $this->admin();

        $good = implode("\n", [
            'tanggal masuk,customer,desainer,status',
            '20/10/2026,Status Sah,Designer Satu,',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($good, 'status-ok.csv')])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 data');

        $this->assertSame(JerseyData::STATUS_PROSES_DESAIN, JerseyData::firstOrFail()->status);

        $bad = implode("\n", [
            'tanggal masuk,customer,desainer,status',
            '20/10/2026,Status Salah,Designer Dua,Lunas',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($bad, 'status-bad.csv')])
            ->assertOk()
            ->assertSee('tidak dikenal')
            ->assertSee('Lunas');

        $this->assertDatabaseCount('jersey_data', 1);
    }

    public function test_status_aliases_from_real_files_map_to_canonical(): void
    {
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer,status',
            '20/10/2026,Alias Pending,Designer A,Pending',
            '20/10/2026,Alias Printing,Designer A,Masuk Printing',
            '20/10/2026,Alias Selesai,Designer A,selesai',
            '20/10/2026,Alias Batal,Designer A,BATAL',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'status-alias.csv')])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 4 data');

        $this->assertSame(JerseyData::STATUS_PROSES_DESAIN, JerseyData::where('customer', 'Alias Pending')->firstOrFail()->status);
        $this->assertSame('Printing', JerseyData::where('customer', 'Alias Printing')->firstOrFail()->status);
        $this->assertSame('Done', JerseyData::where('customer', 'Alias Selesai')->firstOrFail()->status);
        $this->assertSame('Cancel', JerseyData::where('customer', 'Alias Batal')->firstOrFail()->status);
    }

    public function test_continuation_rows_inherit_required_columns_with_warnings(): void
    {
        // Pola file nyata: order lanjutan (varian beda) mengosongkan kolom wajib.
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer,jenis kerah,status',
            '20/10/2026,ARBI PAIJO - 1810,IMUL,V-Neck,Jahit',
            ',,,O-Neck,Jahit',
            ',,IMUL,Round Neck,Printing',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'lanjutan.csv')]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 3 data')
            ->assertSee('Baris 3: tanggal masuk diwariskan dari baris sebelumnya')
            ->assertSee('Baris 3: customer diwariskan dari baris sebelumnya (ARBI PAIJO - 1810)')
            ->assertSee('Baris 3: desainer diwariskan dari baris sebelumnya (IMUL)');

        $this->assertDatabaseCount('jersey_data', 3);

        $variant = JerseyData::where('jenis_kerah', 'O-Neck')->firstOrFail();
        $this->assertSame('ARBI PAIJO - 1810', $variant->customer);
        $this->assertSame('IMUL', $variant->desainer);
        $this->assertSame('2026-10-20', $variant->tanggal_masuk->format('Y-m-d'));

        // Baris 3 mewarisi customer+tanggal; baris 4 hanya mewarisi tanggal
        // (desainer diisi sendiri).
        $third = JerseyData::where('jenis_kerah', 'Round Neck')->firstOrFail();
        $this->assertSame('ARBI PAIJO - 1810', $third->customer);
        $this->assertSame('IMUL', $third->desainer);
    }

    public function test_first_row_still_requires_required_columns(): void
    {
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer',
            ',Tanpa Tanggal,Designer A',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'baris-pertama.csv')])
            ->assertOk()
            ->assertSee('Impor dibatalkan')
            ->assertSee('Baris 2')
            ->assertSee('Tanggal masuk wajib diisi');

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_deadline_text_and_day_month_without_year(): void
    {
        // Teks tujuan ("DESAIN ONLY") di kolom deadline -> tanpa tanggal + warning.
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer,deadline',
            '20/10/2026,Deadline Teks,Designer A,DESAIN ONLY',
            '20/10/2026,Deadline Tanpa Tahun,Designer B,24/10',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'deadline-khusus.csv')]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 2 data')
            ->assertSee('deadline "DESAIN ONLY" bukan tanggal — diabaikan');

        $teks = JerseyData::where('customer', 'Deadline Teks')->firstOrFail();
        $this->assertNull($teks->deadline);

        $tahun = JerseyData::where('customer', 'Deadline Tanpa Tahun')->firstOrFail();
        $this->assertSame(now()->year.'-10-24', $tahun->deadline->format('Y-m-d'));
    }

    public function test_invalid_row_blocks_entire_import_transaktional(): void
    {
        // Baris 3: tanggal terisi tapi rollover (31/02) — tidak ikut warisan,
        // tetap error, dan seluruh impor dibatalkan (baris valid ikut batal).
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer',
            '20/10/2026,Pelanggan Valid,Designer A',
            '31/02/2026,Pelanggan Rusak,Designer B',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'invalid.csv')]);

        $response->assertOk()
            ->assertSee('Impor dibatalkan')
            ->assertSee('Baris 3')
            ->assertSee('Tanggal masuk wajib diisi');

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_deadline_before_tanggal_masuk_is_rejected(): void
    {
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer,deadline',
            '20/10/2026,Deadline Salah,Designer A,01/10/2026',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'deadline.csv')])
            ->assertOk()
            ->assertSee('Deadline tidak boleh sebelum tanggal masuk');

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_missing_required_column_is_fatal(): void
    {
        $csv = "tanggal masuk,customer\n20/10/2026,Tanpa Desainer\n";

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'kurang.csv')])
            ->assertOk()
            ->assertSee('Kolom wajib tidak ditemukan')
            ->assertSee('desainer');

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_blank_lines_skipped_and_bad_date_names_real_line(): void
    {
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer',
            '20/10/2026,Baik,Designer A',
            '',
            '31/02/2026,Tanggal Rusak,Designer A',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'baris.csv')])
            ->assertOk()
            ->assertSee('Baris 4')
            ->assertSee('Tanggal masuk wajib diisi');

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_existing_master_not_duplicated_on_import(): void
    {
        MasterOption::create(['kind' => MasterOption::KIND_DESIGNER, 'name' => 'Budi']);

        $csv = implode("\n", [
            'tanggal masuk,customer,desainer',
            '20/10/2026,Master Duplikat,BUDI',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'duplikat.csv')]);

        $response->assertOk()->assertSee('Berhasil mengimpor 1 data');

        // Case-insensitive: nama master yang sudah ada tidak digandakan.
        $this->assertSame(1, MasterOption::where('kind', MasterOption::KIND_DESIGNER)->count());
        $this->assertDatabaseMissing('options', ['kind' => MasterOption::KIND_DESIGNER, 'name' => 'BUDI']);
    }

    public function test_csv_import_with_pure_canonical_headers(): void
    {
        $csv = implode("\n", [
            'tanggal masuk,customer,desainer,deadline,jenis kerah,jenis bahan,model,status,catatan',
            '25/10/2026,Budi Santoso,ARYO,01/11/2026,Rank,Ovo,Match,Done,Beres',
        ]);

        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.jersey.import.store'), ['file' => $this->makeFile($csv, 'kanonik.csv')])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 data');

        $entry = JerseyData::firstOrFail();

        $this->assertSame('2026-10-25', $entry->tanggal_masuk->format('Y-m-d'));
        $this->assertSame('2026-11-01', $entry->deadline->format('Y-m-d'));
        $this->assertSame('Done', $entry->status);
        $this->assertSame('Beres', $entry->catatan);
    }
}
