<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 20px; }
        body { font-family: notokhmer; font-size: 10px; }
        h1 { margin: 0 0 5px; text-align: center; font-size: 18px; }
        .meta { margin-bottom: 15px; text-align: center; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background-color: #eeeeee; text-align: center; }
        th, td { border: 1px solid #cccccc; padding: 5px; vertical-align: middle; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>Supplier List</h1>
    <div class="meta">Generated: {{ $date }}</div>
