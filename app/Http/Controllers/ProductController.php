<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ProductController extends Controller
{
    /**
     * Menampilkan semua product
     * + Search nama / deskripsi
     * + Filter range harga
     * + Filter status
     */
    public function index(Request $request)
    {
        // Ambil nilai filter dari URL
        $search = trim((string) $request->query('search', ''));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $status = $request->query('status');

        // Query awal
        $query = Product::query();

        /*
        |--------------------------------------------------------------------------
        | Search Nama atau Deskripsi
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | search = gaming
        |
        | Akan mencari product yang:
        | - namanya mengandung "gaming"
        | ATAU
        | - deskripsinya mengandung "gaming"
        |
        */

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Harga Minimum
        |--------------------------------------------------------------------------
        */

        if (
            $minPrice !== null &&
            $minPrice !== '' &&
            is_numeric($minPrice)
        ) {
            $query->where('price', '>=', (float) $minPrice);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Harga Maksimum
        |--------------------------------------------------------------------------
        */

        if (
            $maxPrice !== null &&
            $maxPrice !== '' &&
            is_numeric($maxPrice)
        ) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        |
        | Aturan status yang digunakan:
        |
        | AKTIF:
        | status = aktif
        | DAN
        | stock > 0
        |
        | TIDAK AKTIF:
        | status = tidak_aktif
        | ATAU
        | stock = 0
        |
        */

        if ($status === 'aktif') {
            $query
                ->where('status', 'aktif')
                ->where('stock', '>', 0);
        }

        if ($status === 'tidak_aktif') {
            $query->where(function ($query) {
                $query
                    ->where('status', 'tidak_aktif')
                    ->orWhere('stock', '<=', 0);
            });
        }

        // Ambil hasil setelah search + filter
        $products = $query
            ->latest()
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,

            // Kirim nilai filter kembali ke Vue
            // supaya input tetap terisi setelah search
            'filters' => [
                'search' => $search,
                'min_price' => $minPrice ?? '',
                'max_price' => $maxPrice ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    /**
     * Export semua product ke PDF
     */
    public function exportPdf()
    {
        $products = Product::latest()->get();

        $pdf = Pdf::loadView('products.pdf', [
            'products' => $products,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('products.pdf');
    }

    /**
     * Export semua product ke Excel
     *
     * Foto tidak ikut di-export.
     */
    public function exportExcel()
    {
        $products = Product::latest()->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Products');

        /*
        |--------------------------------------------------------------------------
        | Header Excel
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Deskripsi');
        $sheet->setCellValue('D1', 'Harga');
        $sheet->setCellValue('E1', 'Stok');
        $sheet->setCellValue('F1', 'Status');

        /*
        |--------------------------------------------------------------------------
        | Data Product
        |--------------------------------------------------------------------------
        */

        $row = 2;

        foreach ($products as $index => $product) {
            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $sheet->setCellValue(
                'B' . $row,
                $product->name
            );

            $sheet->setCellValue(
                'C' . $row,
                $product->description ?? '-'
            );

            $sheet->setCellValue(
                'D' . $row,
                (float) $product->price
            );

            $sheet->setCellValue(
                'E' . $row,
                $product->stock
            );

            $sheet->setCellValue(
                'F' . $row,
                $product->status === 'tidak_aktif'
                    ? 'Tidak Aktif'
                    : 'Aktif'
            );

            $row++;
        }

        $lastRow = max(1, $row - 1);

        /*
        |--------------------------------------------------------------------------
        | Style Header
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'ARGB' => 'FFFFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'ARGB' => 'FF2563EB',
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Border
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A1:F' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        /*
        |--------------------------------------------------------------------------
        | Format Harga
        |--------------------------------------------------------------------------
        */

        if ($lastRow >= 2) {
            $sheet
                ->getStyle('D2:D' . $lastRow)
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        /*
        |--------------------------------------------------------------------------
        | Auto Size
        |--------------------------------------------------------------------------
        */

        foreach (range('A', 'F') as $column) {
            $sheet
                ->getColumnDimension($column)
                ->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | Freeze Header
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A2');

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(
            'A1:F' . $lastRow
        );

        /*
        |--------------------------------------------------------------------------
        | Nama File
        |--------------------------------------------------------------------------
        */

        $fileName =
            'products-' .
            now()->format('Y-m-d-H-i-s') .
            '.xlsx';

        /*
        |--------------------------------------------------------------------------
        | Download Excel
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);

                $writer->save('php://output');

                $spreadsheet->disconnectWorksheets();
            },
            $fileName,
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /**
     * Import product dari Excel
     */
    public function importExcel(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi File
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'file' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls',
                    'max:5120',
                ],
            ],
            [
                'file.required' =>
                'Silakan pilih file Excel terlebih dahulu.',

                'file.file' =>
                'File yang dipilih tidak valid.',

                'file.mimes' =>
                'File harus berformat .xlsx atau .xls.',

                'file.max' =>
                'Ukuran file Excel maksimal 5 MB.',
            ]
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Baca File Excel
            |--------------------------------------------------------------------------
            */

            $file = $request->file('file');

            $spreadsheet = IOFactory::load(
                $file->getRealPath()
            );

            $sheet = $spreadsheet->getActiveSheet();

            /*
            |--------------------------------------------------------------------------
            | Cek Header
            |--------------------------------------------------------------------------
            */

            $headerNama = strtolower(
                trim(
                    (string) $sheet
                        ->getCell('A1')
                        ->getValue()
                )
            );

            $headerDeskripsi = strtolower(
                trim(
                    (string) $sheet
                        ->getCell('B1')
                        ->getValue()
                )
            );

            $headerHarga = strtolower(
                trim(
                    (string) $sheet
                        ->getCell('C1')
                        ->getValue()
                )
            );

            $headerStok = strtolower(
                trim(
                    (string) $sheet
                        ->getCell('D1')
                        ->getValue()
                )
            );

            $headerStatus = strtolower(
                trim(
                    (string) $sheet
                        ->getCell('E1')
                        ->getValue()
                )
            );

            $headersValid =
                $headerNama === 'nama'
                && $headerDeskripsi === 'deskripsi'
                && $headerHarga === 'harga'
                && $headerStok === 'stok';

            if (! $headersValid) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'file' =>
                    'Format Excel tidak sesuai. Header wajib: Nama | Deskripsi | Harga | Stok. Status bersifat opsional.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Apakah file memiliki kolom Status?
            |--------------------------------------------------------------------------
            */

            $hasStatusColumn =
                $headerStatus === 'status';

            /*
            |--------------------------------------------------------------------------
            | Ambil Baris Terakhir
            |--------------------------------------------------------------------------
            */

            $highestRow = $sheet->getHighestDataRow();

            if ($highestRow < 2) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'file' =>
                    'File Excel tidak memiliki data product.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Siapkan Data Import
            |--------------------------------------------------------------------------
            */

            $productsToImport = [];
            $rowErrors = [];

            /*
            |--------------------------------------------------------------------------
            | Baca Setiap Baris
            |--------------------------------------------------------------------------
            */

            for ($row = 2; $row <= $highestRow; $row++) {

                $name = trim(
                    (string) $sheet
                        ->getCell('A' . $row)
                        ->getValue()
                );

                $description = trim(
                    (string) $sheet
                        ->getCell('B' . $row)
                        ->getValue()
                );

                $price = $sheet
                    ->getCell('C' . $row)
                    ->getValue();

                $stock = $sheet
                    ->getCell('D' . $row)
                    ->getValue();

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $status = 'aktif';

                if ($hasStatusColumn) {
                    $statusExcel = strtolower(
                        trim(
                            (string) $sheet
                                ->getCell('E' . $row)
                                ->getValue()
                        )
                    );

                    if (
                        $statusExcel === 'aktif'
                        || $statusExcel === ''
                    ) {
                        $status = 'aktif';
                    } elseif (
                        $statusExcel === 'tidak aktif'
                        || $statusExcel === 'tidak_aktif'
                    ) {
                        $status = 'tidak_aktif';
                    } else {
                        $rowErrors[] =
                            "Baris {$row}: Status harus Aktif atau Tidak Aktif.";

                        continue;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Lewati Baris Kosong
                |--------------------------------------------------------------------------
                */

                $isEmpty =
                    $name === ''
                    && $description === ''
                    && ($price === null || $price === '')
                    && ($stock === null || $stock === '');

                if ($isEmpty) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Validasi Nama
                |--------------------------------------------------------------------------
                */

                if ($name === '') {
                    $rowErrors[] =
                        "Baris {$row}: Nama wajib diisi.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Validasi Harga
                |--------------------------------------------------------------------------
                */

                if (! is_numeric($price)) {
                    $rowErrors[] =
                        "Baris {$row}: Harga harus berupa angka.";

                    continue;
                }

                if ((float) $price < 0) {
                    $rowErrors[] =
                        "Baris {$row}: Harga tidak boleh negatif.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Validasi Stok
                |--------------------------------------------------------------------------
                */

                if (! is_numeric($stock)) {
                    $rowErrors[] =
                        "Baris {$row}: Stok harus berupa angka.";

                    continue;
                }

                if ((float) $stock < 0) {
                    $rowErrors[] =
                        "Baris {$row}: Stok tidak boleh negatif.";

                    continue;
                }

                if (
                    floor((float) $stock)
                    != (float) $stock
                ) {
                    $rowErrors[] =
                        "Baris {$row}: Stok harus berupa bilangan bulat.";

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Data Siap Import
                |--------------------------------------------------------------------------
                */

                $productsToImport[] = [
                    'name' => $name,

                    'description' =>
                    $description !== ''
                        ? $description
                        : null,

                    'price' => (float) $price,

                    'stock' => (int) $stock,

                    'status' => $status,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Jika Ada Error
            |--------------------------------------------------------------------------
            */

            if (! empty($rowErrors)) {
                $spreadsheet->disconnectWorksheets();

                $errors = array_slice(
                    $rowErrors,
                    0,
                    5
                );

                $message = implode(
                    ' ',
                    $errors
                );

                if (count($rowErrors) > 5) {
                    $message .=
                        ' Dan terdapat ' .
                        (count($rowErrors) - 5) .
                        ' error lainnya.';
                }

                return back()->withErrors([
                    'file' => $message,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Pastikan Ada Data
            |--------------------------------------------------------------------------
            */

            if (empty($productsToImport)) {
                $spreadsheet->disconnectWorksheets();

                return back()->withErrors([
                    'file' =>
                    'Tidak ada data product yang dapat di-import.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan ke Database
            |--------------------------------------------------------------------------
            */

            DB::transaction(
                function () use ($productsToImport) {

                    foreach (
                        $productsToImport
                        as $productData
                    ) {
                        Product::create(
                            $productData
                        );
                    }
                }
            );

            $totalImported =
                count($productsToImport);

            $spreadsheet->disconnectWorksheets();

            return redirect()
                ->route('products.index')
                ->with(
                    'success',
                    "{$totalImported} product berhasil di-import dari Excel."
                );
        } catch (Throwable $exception) {

            report($exception);

            return back()->withErrors([
                'file' =>
                'File Excel gagal dibaca. Pastikan file tidak rusak dan formatnya benar.',
            ]);
        }
    }

    /**
     * Menampilkan form tambah product
     */
    public function create()
    {
        return Inertia::render(
            'Products/Create'
        );
    }

    /**
     * Menyimpan product baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' =>
            'required|string|max:255',

            'description' =>
            'nullable|string',

            'price' =>
            'required|numeric|min:0',

            'stock' =>
            'required|integer|min:0',

            'status' =>
            'required|in:aktif,tidak_aktif',

            'image' =>
            'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] =
                $request
                ->file('image')
                ->store(
                    'products',
                    'public'
                );
        }

        Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail product
     */
    public function show(Product $product)
    {
        return Inertia::render(
            'Products/Show',
            [
                'product' => $product,
            ]
        );
    }

    /**
     * Menampilkan form edit
     */
    public function edit(Product $product)
    {
        return Inertia::render(
            'Products/Edit',
            [
                'product' => $product,
            ]
        );
    }

    /**
     * Update product
     */
    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'name' =>
            'required|string|max:255',

            'description' =>
            'nullable|string',

            'price' =>
            'required|numeric|min:0',

            'stock' =>
            'required|integer|min:0',

            'status' =>
            'required|in:aktif,tidak_aktif',

            'image' =>
            'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($product->image) {
                Storage::disk('public')
                    ->delete(
                        $product->image
                    );
            }

            $validated['image'] =
                $request
                ->file('image')
                ->store(
                    'products',
                    'public'
                );
        }

        $product->update(
            $validated
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil diperbarui.'
            );
    }

    /**
     * Hapus product
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')
                ->delete(
                    $product->image
                );
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Product berhasil dihapus.'
            );
    }

    // buat halaman new product
    public function newProducts(Request $request)
    {
        // Ambil nilai filter dari URL
        $search = trim((string) $request->query('search', ''));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $status = $request->query('status');

        // Query awal
        $query = Product::query();

        /*
        |--------------------------------------------------------------------------
        | Search Nama atau Deskripsi
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | search = gaming
        |
        | Akan mencari product yang:
        | - namanya mengandung "gaming"
        | ATAU
        | - deskripsinya mengandung "gaming"
        |
        */

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Harga Minimum
        |--------------------------------------------------------------------------
        */

        if (
            $minPrice !== null &&
            $minPrice !== '' &&
            is_numeric($minPrice)
        ) {
            $query->where('price', '>=', (float) $minPrice);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Harga Maksimum
        |--------------------------------------------------------------------------
        */

        if (
            $maxPrice !== null &&
            $maxPrice !== '' &&
            is_numeric($maxPrice)
        ) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        |
        | Aturan status yang digunakan:
        |
        | AKTIF:
        | status = aktif
        | DAN
        | stock > 0
        |
        | TIDAK AKTIF:
        | status = tidak_aktif
        | ATAU
        | stock = 0
        |
        */

        if ($status === 'aktif') {
            $query
                ->where('status', 'aktif')
                ->where('stock', '>', 0);
        }

        if ($status === 'tidak_aktif') {
            $query->where(function ($query) {
                $query
                    ->where('status', 'tidak_aktif')
                    ->orWhere('stock', '<=', 0);
            });
        }

        // Ambil hasil setelah search + filter
        $products = $query
            ->latest()
            ->get();

        return Inertia::render('Products/NewIndex', [
            'products' => $products,

            // Kirim nilai filter kembali ke Vue
            // supaya input tetap terisi setelah search
            'filters' => [
                'search' => $search,
                'min_price' => $minPrice ?? '',
                'max_price' => $maxPrice ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    public function newCreate()
    {
        return Inertia::render(
            'Products/NewCreate'
        );
    }

    // buat halaman new edit
    public function newEdit(Product $product)
    {
        return Inertia::render(
            'Products/NewEdit',
            [
                'product' => $product,
            ]
        );
    }
}
