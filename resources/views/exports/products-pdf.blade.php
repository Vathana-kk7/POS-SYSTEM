<!DOCTYPE html>
<html lang="km">

<head>
    <meta charset="UTF-8">

   
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: notokhmer;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            margin: 0 0 5px 0;
        }

        .meta {
            text-align: center;
            font-size: 9px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background-color: #eeeeee;
            font-weight: bold;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .low {
            color: red;
            font-weight: bold;
        }

        .no-img {
            color: #999999;
            font-size: 8px;
        }

        .product-img {
            width: 45px;
            height: 45px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

{{-- =========================================================
      PDF HEADER
========================================================= --}}

@if ($headerOnly)

    <h1>
        Product List
    </h1>

    <div class="meta">
        Generated: {{ $date }}
    </div>

@endif


{{-- =========================================================
      PRODUCT TABLE
========================================================= --}}

@if ($chunkOnly)

    <table>

        <thead>
            <tr>

                <th width="5%">
                    #
                </th>

                <th width="10%">
                    Image
                </th>

                <th width="18%">
                    Product Name
                </th>

                <th width="15%">
                    Barcode
                </th>

                <th width="28%">
                    Description
                </th>

                <th width="14%">
                    Selling Price
                </th>

                <th width="10%">
                    Stock Qty
                </th>

            </tr>
        </thead>

        <tbody>

            @forelse ($products as $product)

                <tr>

                    {{-- INDEX --}}
                    <td class="text-center">
                        {{ $product->pdf_index }}
                    </td>


                    {{-- IMAGE --}}
                    <td class="text-center">

                        @if (!empty($product->image_base64))

                            <img
                                src="{{ $product->image_base64 }}"
                                class="product-img"
                            >

                        @else

                            <span class="no-img">
                                No image
                            </span>

                        @endif

                    </td>


                    {{-- PRODUCT NAME --}}
                    <td>
                        {{ $product->name }}
                    </td>


                    {{-- BARCODE --}}
                    <td>
                        {{ $product->barcode ?? $product->sku ?? '-' }}
                    </td>


                    {{-- DESCRIPTION --}}
                    <td>
                        {{ \Illuminate\Support\Str::limit(
                            $product->description ?? '',
                            50
                        ) }}
                    </td>


                    {{-- SELLING PRICE --}}
                    <td class="text-right">

                        ${{ number_format(
                            (float) $product->selling_price,
                            2
                        ) }}

                    </td>


                    {{-- STOCK --}}
                    <td
                        class="text-center
                        {{
                            $product->stock_qty <= $product->min_stock_level
                                ? 'low'
                                : ''
                        }}"
                    >

                        {{ $product->stock_qty }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="7"
                        class="text-center"
                    >
                        No products found
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

@endif

</body>
</html>
