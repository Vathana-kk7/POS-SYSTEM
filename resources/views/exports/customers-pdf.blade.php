<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        h1 {
            margin: 0 0 5px;
            text-align: center;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 15px;
            text-align: center;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background-color: #eeeeee;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <h1>Customer List</h1>
    <div class="meta">Generated: {{ $date }}</div>

    <table>
        <thead>
            <tr>
                <th width="20%">Name</th>
                <th width="14%">Phone</th>
                <th width="20%">Email</th>
                <th width="26%">Address</th>
                <th width="10%">Group</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->email ?: '-' }}</td>
                    <td>{{ $customer->address ?: '-' }}</td>
                    <td>{{ $customer->group }}</td>
                    <td>{{ $customer->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
