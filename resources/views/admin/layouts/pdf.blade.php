<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .header h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            color: #7f8c8d;
            margin: 10px 0 0;
            font-size: 14px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .table tr:hover {
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .table-active {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        h4 {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .total-row td {
            border-top: 2px solid #eee;
        }
        .currency {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        .percentage {
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html> 