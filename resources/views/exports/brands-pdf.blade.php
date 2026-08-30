<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Brands Report</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
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
            background-color: #eeeeee;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 6px;
        }

        .active {
            color: green;
        }

        .inactive {
            color: red;
        }

        @page {
            margin: 15mm;
        }
    </style>
</head>

<body>

    <h1>Brand List</h1>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Brand Name</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($brands as $index => $brand)

                <tr>
                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $brand->name }}
                    </td>

                    <td class="{{ $brand->status }}">
                        {{ ucfirst($brand->status) }}
                    </td>

                    <td>
                        {{ $brand->created_at?->format('Y-m-d H:i') }}
                    </td>
                </tr>

            @endforeach

        </tbody>
    </table>

</body>
</html>
