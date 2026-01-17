<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Report')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .header p {
            font-size: 9pt;
            color: #7f8c8d;
        }

        .report-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 4px;
        }

        .report-info table {
            width: 100%;
        }

        .report-info td {
            padding: 3px 5px;
            font-size: 9pt;
        }

        .report-info td:first-child {
            font-weight: bold;
            width: 30%;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th {
            background-color: #34495e;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
        }

        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.data-table tr:hover {
            background-color: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f4f8;
            border-left: 4px solid #3498db;
        }

        .summary-box h3 {
            font-size: 12pt;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #bdc3c7;
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 11pt;
            margin-top: 5px;
            padding-top: 10px;
            border-top: 2px solid #2c3e50;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #7f8c8d;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-success {
            background-color: #27ae60;
            color: white;
        }

        .badge-warning {
            background-color: #f39c12;
            color: white;
        }

        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }

        .badge-info {
            background-color: #3498db;
            color: white;
        }

        h2 {
            font-size: 14pt;
            margin: 15px 0 10px 0;
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>@yield('report-title', 'Business Report')</h1>
        <p>Multi-Section Food Business Management System</p>
    </div>

    @yield('content')

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t h:i A') }} | Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>

</html>