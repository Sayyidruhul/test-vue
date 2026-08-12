<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Daftar Products</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .product-image {
            width: 55px;
            height: 55px;
        }

        .no-image {
            color: #999;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <h1>Daftar Products</h1>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">
                    No
                </th>

                <th width="12%" class="text-center">
                    Foto
                </th>

                <th width="18%">
                    Nama
                </th>

                <th width="30%">
                    Deskripsi
                </th>

                <th width="20%">
                    Harga
                </th>

                <th width="10%" class="text-center">
                    Stok
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($products as $index => $product)

                <tr>

                    <!-- No -->
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <!-- Foto -->
                    <td class="text-center">

                        @php
                            $imageSrc = null;

                            if ($product->image) {

                                $imagePath = storage_path(
                                    'app/public/' . $product->image
                                );

                                if (file_exists($imagePath)) {

                                    $mimeType = mime_content_type($imagePath);

                                    $imageSrc =
                                        'data:' .
                                        $mimeType .
                                        ';base64,' .
                                        base64_encode(
                                            file_get_contents($imagePath)
                                        );
                                }
                            }
                        @endphp

                        @if ($imageSrc)

                            <img
                                src="{{ $imageSrc }}"
                                alt="{{ $product->name }}"
                                class="product-image"
                            >

                        @else

                            <span class="no-image">
                                No Image
                            </span>

                        @endif

                    </td>

                    <!-- Nama -->
                    <td>
                        {{ $product->name }}
                    </td>

                    <!-- Deskripsi -->
                    <td>
                        {{ $product->description ?? '-' }}
                    </td>

                    <!-- Harga -->
                    <td class="text-right">
                        Rp {{ number_format(
                            $product->price,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <!-- Stok -->
                    <td class="text-center">
                        {{ $product->stock }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td
                        colspan="6"
                        class="text-center"
                    >
                        Belum ada product.
                    </td>
                </tr>

            @endforelse
        </tbody>
    </table>

</body>
</html>