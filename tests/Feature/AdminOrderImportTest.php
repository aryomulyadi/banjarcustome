<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\CustomOrder;
use App\Models\MasterOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class AdminOrderImportTest extends TestCase
{
    use RefreshDatabase;

    /** Judul kolom format baru (urutan sengaja diacak pada sebagian test). */
    private const HEADERS = [
        'BULAN', 'customer', 'jenis', 'waktu pengambilan', 'type', 'bahan', 'warna',
        'pesan bahan', 'produksi', 'finish', 'TOTAL', 'jumlah', 'ukuran',
        'whatsapp', 'status', 'catatan',
    ];

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeFile(string $content, string $filename): UploadedFile
    {
        $path = sys_get_temp_dir().'/bc-import-'.uniqid('', true).'-'.$filename;

        file_put_contents($path, $content);

        $mime = str_ends_with($filename, '.csv')
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return new UploadedFile($path, $filename, $mime, null, true);
    }

    private function makeXlsx(array $rows): UploadedFile
    {
        $path = sys_get_temp_dir().'/bc-import-'.uniqid('', true).'.xlsx';

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
            'pesanan.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    public function test_import_form_and_template_require_admin_and_download(): void
    {
        $this->get(route('admin.orders.import'))->assertRedirect(route('login'));

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.orders.import'))
            ->assertOk()
            ->assertSee('Unggah File')
            ->assertSee('customer');

        $this->actingAs($admin)
            ->get(route('admin.orders.import.template'))
            ->assertOk()
            ->assertDownload('template-pesanan.xlsx');
    }

    public function test_valid_csv_import_maps_new_format_end_to_end(): void
    {
        $csv = implode("\n", [
            'BULAN, customer, jenis, waktu pengambilan, type, bahan, warna, pesan bahan, produksi, finish, TOTAL, jumlah, ukuran, whatsapp, status, catatan',
            'Oktober 2026, Budi Santoso, Bordir, 20/10/2026, Kaos Lengan Panjang, Cotton Combed 30s, Navy, ok, OK, nok, "1.500.000", 24, , 081234567890, completed, Pesanan lama via Excel',
            'November 2026, Siti Aminah, , , , , , , , , , 10, , 081200001111, production,',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'pesanan.csv'),
            ]);

        $response->assertOk()->assertSee('Berhasil mengimpor 2 pesanan');

        // PRG: POST tidak boleh mengembalikan view langsung (refresh = duplikat).
        $this->actingAs($this->admin())
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'pesanan-2.csv'),
            ])
            ->assertRedirect(route('admin.orders.import'));

        $this->assertDatabaseCount('custom_orders', 4);

        $first = CustomOrder::where('name', 'Budi Santoso')->firstOrFail();

        $this->assertSame(CustomOrder::SOURCE_IMPORT, $first->source);
        // BULAN menurunkan tanggal pesanan: 1 Oktober 2026.
        $this->assertSame('2026-10-01', $first->created_at->format('Y-m-d'));
        $this->assertSame('2026-10-20', $first->deadline->format('Y-m-d'));
        $this->assertSame(CustomOrder::STATUS_COMPLETED, $first->status);
        $this->assertSame(1500000, $first->total_price);
        $this->assertSame(24, $first->quantity);
        $this->assertSame('Bordir', $first->service_type);
        $this->assertSame('Kaos Lengan Panjang', $first->type);
        $this->assertSame('Cotton Combed 30s', $first->bahan);
        $this->assertSame('Navy', $first->warna);
        $this->assertTrue($first->pesan_bahan_ok);
        $this->assertTrue($first->produksi_ok);
        $this->assertFalse($first->finish_ok);
        $this->assertSame('Pesanan lama via Excel', $first->notes);
        $this->assertNotNull($first->tracking_token);
        $this->assertDatabaseHas('order_status_history', [
            'custom_order_id' => $first->id,
            'to_status' => CustomOrder::STATUS_COMPLETED,
        ]);

        // Spec unik pada baris impor otomatis menjadi master data + dilaporkan.
        $response->assertSee('Master data baru dibuat')
            ->assertSee('Kaos Lengan Panjang')
            ->assertSee('Cotton Combed 30s')
            ->assertSee('Navy');

        $this->assertSame(3, MasterOption::count());

        $second = CustomOrder::where('name', 'Siti Aminah')->firstOrFail();

        $this->assertSame('2026-11-01', $second->created_at->format('Y-m-d'));
        $this->assertSame(10, $second->quantity);
        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $second->status);
        $this->assertFalse($second->pesan_bahan_ok);
    }

    public function test_headers_matched_case_insensitively_in_any_order_with_extra_columns(): void
    {
        $rows = [
            ['Kolom Asing', 'CUSTOMER', 'Waktu Pengambilan', 'jumlah', 'Kolom Lain', 'ukuran'],
            ['x', 'Andi Wijaya', '07/10/2026', 5, 'y', 'S:3, M:2'],
        ];

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeXlsx($rows),
            ]);

        $response->assertOk()->assertSee('Berhasil mengimpor 1 pesanan');

        $order = CustomOrder::where('name', 'Andi Wijaya')->firstOrFail();

        $this->assertSame(CustomOrder::SOURCE_IMPORT, $order->source);
        $this->assertSame(5, $order->quantity);
        $this->assertSame('2026-10-07', $order->deadline->format('Y-m-d'));
        $this->assertSame(['S' => 3, 'M' => 2], $order->size_quantities);
    }

    public function test_alias_headings_from_real_files_are_matched_without_template(): void
    {
        // File nyata: semua judul alternatif, tanpa satu pun judul kanonik resmi.
        $rows = [
            [
                'Bulan Pesanan', 'Nama Customer', 'Jenis Pesanan', 'Deadline', 'Tipe',
                'Material', 'Color', 'Pesanan Bahan', 'Progres Produksi', 'Finishing',
                'Total Harga', 'Qty', 'Size', 'No HP', 'Status Pesanan', 'Keterangan',
            ],
            [
                'Oktober 2026', 'Rina Marlina', 'Bordir', '20/10/2026', 'Kaos Lengan Panjang',
                'Cotton Combed 30s', 'Navy', 'ya', 'ya', 'tidak',
                '1.500.000', '24', 'S:6, M:18', '081234567890', 'Selesai', 'via excel',
            ],
        ];

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeXlsx($rows),
            ]);

        $response->assertOk()->assertSee('Berhasil mengimpor 1 pesanan');

        $order = CustomOrder::where('name', 'Rina Marlina')->firstOrFail();

        $this->assertSame(CustomOrder::SOURCE_IMPORT, $order->source);
        $this->assertSame('Bordir', $order->service_type);
        $this->assertSame('Kaos Lengan Panjang', $order->type);
        $this->assertSame('Cotton Combed 30s', $order->bahan);
        $this->assertSame('Navy', $order->warna);
        $this->assertSame('2026-10-20', $order->deadline->format('Y-m-d'));
        $this->assertSame('2026-10-01', $order->created_at->format('Y-m-d'));
        $this->assertTrue($order->pesan_bahan_ok);
        $this->assertTrue($order->produksi_ok);
        $this->assertFalse($order->finish_ok);
        $this->assertSame(1500000, $order->total_price);
        $this->assertSame(24, $order->quantity);
        $this->assertSame(['S' => 6, 'M' => 18], $order->size_quantities);
        $this->assertSame('081234567890', $order->whatsapp_number);
        $this->assertSame(CustomOrder::STATUS_COMPLETED, $order->status);
        $this->assertSame('via excel', $order->notes);
    }

    public function test_invalid_row_blocks_entire_import(): void
    {
        $csv = implode("\n", [
            'customer,status',
            'Pelanggan Valid,completed',
            'Pelanggan Salah,lunas',
        ]);

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'pesanan.csv'),
            ]);

        $response->assertOk()
            ->assertSee('Impor dibatalkan')
            ->assertSee('Baris 3');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_missing_required_column_returns_fatal_and_import_rejects_bad_extension(): void
    {
        $admin = $this->admin();

        $csv = "name,whatsapp\nBudi,081111111111\n";

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'pesanan.csv'),
            ])
            ->assertOk()
            ->assertSee('Kolom wajib tidak ditemukan')
            ->assertSee('customer');

        $this->assertDatabaseCount('custom_orders', 0);

        $txt = $this->makeFile('bukan spreadsheet', 'pesanan.txt');

        $this->actingAs($admin)
            ->post(route('admin.orders.import.store'), ['file' => $txt])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_orders_index_source_filter_and_badge(): void
    {
        $admin = $this->admin();

        CustomOrder::create([
            'name' => 'Pesanan Formulir',
            'whatsapp_number' => '081111111111',
            'source' => CustomOrder::SOURCE_FORM,
        ]);

        CustomOrder::create([
            'name' => 'Pesanan Hasil Impor',
            'whatsapp_number' => '081111111112',
            'source' => CustomOrder::SOURCE_IMPORT,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['source' => 'import']))
            ->assertOk()
            ->assertSee('Pesanan Hasil Impor')
            ->assertDontSee('Pesanan Formulir');

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['source' => 'form']))
            ->assertOk()
            ->assertSee('Pesanan Formulir')
            ->assertDontSee('Pesanan Hasil Impor');

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('>Impor</span>', false);
    }

    public function test_rollover_date_and_bad_amount_are_rejected(): void
    {
        $admin = $this->admin();

        $rollover = implode("\n", [
            'customer,waktu pengambilan',
            'Rollover,31/02/2026',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($rollover, 'rollover.csv'),
            ])
            ->assertOk()
            ->assertSee('Waktu pengambilan tidak valid');

        $this->assertDatabaseCount('custom_orders', 0);

        $badAmount = implode("\n", [
            'customer,TOTAL',
            'Harga Desimal,45000.5',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($badAmount, 'desimal.csv'),
            ])
            ->assertOk()
            ->assertSee('TOTAL tidak valid');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_placeholder_whatsapp_and_blank_lines_keep_real_line_numbers(): void
    {
        $admin = $this->admin();

        $csv = implode("\n", [
            'customer,whatsapp,status',
            'Pelanggan Baik,081111111115,pending',
            '',
            'Pelanggan Placeholder,-----,pending',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'placeholder.csv'),
            ])
            ->assertOk()
            ->assertSee('Baris 4')
            ->assertSee('WhatsApp tidak valid');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_jenis_asing_is_rejected_but_known_jenis_is_accepted(): void
    {
        $admin = $this->admin();

        $unknown = implode("\n", [
            'customer,jenis',
            'Jenis Salah,Sablon Emas 24 Karat',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($unknown, 'jenis.csv'),
            ])
            ->assertOk()
            ->assertSee('tidak dikenal')
            ->assertSee('Sablon Emas 24 Karat');

        $this->assertDatabaseCount('custom_orders', 0);

        $known = implode("\n", [
            'customer,jenis',
            'Jenis Valid,bordir',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($known, 'jenis-ok.csv'),
            ])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 pesanan');

        // Casing mengikuti judul resmi dari Pengaturan.
        $this->assertSame('Bordir', CustomOrder::firstOrFail()->service_type);
    }

    public function test_progress_flag_values_parse_ok_nok_and_reject_unknown(): void
    {
        $admin = $this->admin();

        $valid = implode("\n", [
            'customer,pesan bahan,produksi,finish',
            'Flag Valid,ya,0,tidak',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($valid, 'flag.csv'),
            ])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 pesanan');

        $order = CustomOrder::firstOrFail();
        $this->assertTrue($order->pesan_bahan_ok);
        $this->assertFalse($order->produksi_ok);
        $this->assertFalse($order->finish_ok);

        $invalid = implode("\n", [
            'customer,pesan bahan',
            'Flag Salah,mungkin',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($invalid, 'flag-salah.csv'),
            ])
            ->assertOk()
            ->assertSee('harus bernilai ok atau nok');

        $this->assertDatabaseCount('custom_orders', 1);
    }

    public function test_month_formats_derive_created_at_and_bad_month_warns(): void
    {
        $admin = $this->admin();

        $csv = implode("\n", [
            'BULAN,customer',
            'Okt 2026,Bulan Singkat',
            '2026-09,Bulan Angka',
            'Kemarin 2026,Bulan Rusak',
        ]);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'bulan.csv'),
            ]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 3 pesanan')
            ->assertSee('BULAN "Kemarin 2026" tidak dikenal');

        $this->assertSame('2026-10-01', CustomOrder::where('name', 'Bulan Singkat')->firstOrFail()->created_at->format('Y-m-d'));
        $this->assertSame('2026-09-01', CustomOrder::where('name', 'Bulan Angka')->firstOrFail()->created_at->format('Y-m-d'));

        // Gagal parse: tetap diimpor, tanggal pesanan = tanggal impor (hari ini).
        $this->assertSame(now()->format('Y-m-d'), CustomOrder::where('name', 'Bulan Rusak')->firstOrFail()->created_at->format('Y-m-d'));
    }

    public function test_quantity_falls_back_to_sum_of_size_quantities(): void
    {
        $admin = $this->admin();

        $csv = implode("\n", [
            'customer,ukuran',
            'Ukuran Only,"S:6, M:12"',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'ukuran.csv'),
            ])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 1 pesanan');

        $order = CustomOrder::firstOrFail();

        $this->assertSame(['S' => 6, 'M' => 12], $order->size_quantities);
        $this->assertSame(18, $order->quantity);
    }

    public function test_bad_size_format_is_rejected(): void
    {
        $admin = $this->admin();

        $csv = implode("\n", [
            'customer,ukuran',
            'Ukuran Salah,S-6',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'ukuran-salah.csv'),
            ])
            ->assertOk()
            ->assertSee('tidak valid (format: S:6, M:12)');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_status_accepts_keys_and_labels_but_rejects_unknown(): void
    {
        $admin = $this->admin();

        $csv = implode("\n", [
            'customer,status',
            'Label Sah,Sedang Diproses',
            'Key Sah,completed',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($csv, 'status-ok.csv'),
            ])
            ->assertOk()
            ->assertSee('Berhasil mengimpor 2 pesanan');

        $this->assertSame(CustomOrder::STATUS_PRODUCTION, CustomOrder::where('name', 'Label Sah')->firstOrFail()->status);
        $this->assertSame(CustomOrder::STATUS_COMPLETED, CustomOrder::where('name', 'Key Sah')->firstOrFail()->status);

        $bad = implode("\n", [
            'customer,status',
            'Status Rusak,dibayar',
        ]);

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), [
                'file' => $this->makeFile($bad, 'status-buruk.csv'),
            ])
            ->assertOk()
            ->assertSee('Status harus salah satu dari');

        $this->assertDatabaseCount('custom_orders', 2);
    }

    public function test_export_then_reimport_roundtrip_preserves_core_fields(): void
    {
        $order = CustomOrder::create([
            'name' => 'Pelanggan Roundtrip',
            'whatsapp_number' => '089876543210',
            'quantity' => 18,
            'deadline' => '2026-10-25',
            'service_type' => 'Bordir',
            'type' => 'Hoodie',
            'bahan' => 'Fleece',
            'warna' => 'Abu',
            'size_quantities' => ['S' => 6, 'M' => 12],
            'total_price' => 1080000,
            'notes' => 'Catatan roundtrip',
            'pesan_bahan_ok' => true,
            'produksi_ok' => true,
            'finish_ok' => false,
        ]);

        $order->forceFill(['status' => CustomOrder::STATUS_PRODUCTION])->save();

        $export = $this->actingAs($this->admin())->get(route('admin.orders.export'));

        // Unggah ulang file ekspor yang sama (format impor = format ekspor).
        $tmp = sys_get_temp_dir().'/bc-roundtrip-'.uniqid('', true).'.xlsx';
        copy($export->baseResponse->getFile()->getPathname(), $tmp);

        $upload = new UploadedFile(
            $tmp,
            'laporan-pesanan.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $response = $this->actingAs($this->admin())
            ->followingRedirects()
            ->post(route('admin.orders.import.store'), ['file' => $upload]);

        $response->assertOk()
            ->assertSee('Berhasil mengimpor 1 pesanan')
            ->assertSee('Hoodie')
            ->assertSee('Fleece')
            ->assertSee('Abu');

        $reimported = CustomOrder::where('name', 'Pelanggan Roundtrip')
            ->where('id', '!=', $order->id)
            ->firstOrFail();

        $this->assertSame($order->whatsapp_number, $reimported->whatsapp_number);
        $this->assertSame(18, $reimported->quantity);
        $this->assertSame('2026-10-25', $reimported->deadline->format('Y-m-d'));
        $this->assertSame('Bordir', $reimported->service_type);
        $this->assertSame('Hoodie', $reimported->type);
        $this->assertSame('Fleece', $reimported->bahan);
        $this->assertSame('Abu', $reimported->warna);
        $this->assertSame(['S' => 6, 'M' => 12], $reimported->size_quantities);
        $this->assertSame(1080000, $reimported->total_price);
        $this->assertSame('Catatan roundtrip', $reimported->notes);
        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $reimported->status);
        $this->assertTrue($reimported->pesan_bahan_ok);
        $this->assertTrue($reimported->produksi_ok);
        $this->assertFalse($reimported->finish_ok);
        // BULAN roundtrip: sama bulannya dengan pesanan asli.
        $this->assertSame($order->created_at->format('Y-m'), $reimported->created_at->format('Y-m'));
        $this->assertSame(CustomOrder::SOURCE_IMPORT, $reimported->source);
        $this->assertSame($order->source, CustomOrder::SOURCE_FORM);
    }

    public function test_template_download_contains_official_headers(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.import.template'));

        $response->assertOk()->assertDownload('template-pesanan.xlsx');

        $path = tempnam(sys_get_temp_dir(), 'bc-tpl-read').'.xlsx';
        file_put_contents($path, $response->baseResponse->getFile()->getContent());

        $reader = new XlsxReader;
        $reader->open($path);

        $headers = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $headers = array_map('strval', $row->toArray());

                break;
            }

            break;
        }

        $reader->close();
        @unlink($path);

        $reflection = new \ReflectionClass(OrderController::class);
        $constant = $reflection->getConstant('IMPORT_HEADERS');

        $this->assertSame($constant, $headers);
        $this->assertSame(self::HEADERS, $headers);
    }

    public function test_status_chips_keep_source_filter_and_counts(): void
    {
        $admin = $this->admin();

        CustomOrder::create([
            'name' => 'Form Pending',
            'whatsapp_number' => '081111111116',
            'source' => CustomOrder::SOURCE_FORM,
            'status' => 'pending',
        ]);

        CustomOrder::create([
            'name' => 'Impor Pending',
            'whatsapp_number' => '081111111117',
            'source' => CustomOrder::SOURCE_IMPORT,
            'status' => 'pending',
        ]);

        CustomOrder::create([
            'name' => 'Impor Selesai',
            'whatsapp_number' => '081111111118',
            'source' => CustomOrder::SOURCE_IMPORT,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.index', ['source' => 'import']));

        $response->assertOk()
            ->assertSee('Impor Pending')
            ->assertDontSee('Form Pending');

        // Chip status mempertahankan filter sumber.
        $this->assertStringContainsString('source=import', $response->getContent());

        // Count mengikuti filter sumber: "Semua" = 2 (bukan total 3).
        $response->assertSee('Semua <span>(2)</span>', false);

        $searchResponse = $this->actingAs($admin)
            ->get(route('admin.orders.index', ['source' => 'import', 'q' => '0']))
            ->assertOk();

        // Pencarian "0" tidak hilang dari link filter/export (array_filter lama membuang "0").
        $this->assertStringContainsString('q=0', $searchResponse->getContent());
        $this->assertStringContainsString('source=import', $searchResponse->getContent());
    }
}
