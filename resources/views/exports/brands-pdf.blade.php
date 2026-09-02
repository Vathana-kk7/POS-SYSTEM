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
                <th style="width: 10%; text-align: center;">No.</th>
                <th>Brand Name</th>
                <th style="width: 15%; text-align: center;">Status</th>
                <th style="width: 25%;">Created At</th>
            </tr>
        </thead>
        <tbody>
