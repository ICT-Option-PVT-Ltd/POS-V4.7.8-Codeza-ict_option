<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Serial Numbers Report</title>
    <style>
        body{font-family:Arial,sans-serif; font-size:12px; margin:12px;}
        .topbar{display:flex; justify-content:space-between; margin-bottom:12px;}
        table{width:100%; border-collapse:collapse; margin-bottom:14px;}
        th,td{border:1px solid #ccc; padding:4px; text-align:left;}
        th{background:#f3f3f3;}
        .loc-title{margin:16px 0 8px; font-size:14px; font-weight:700;}
        @media print {.no-print{display:none;}}
    </style>
</head>
<body>
    <div class="topbar no-print">
        <div><strong>Serial Numbers Location-wise Report</strong></div>
        <div>
            <button onclick="window.print()">Print</button>
        </div>
    </div>

    <div>
        <strong>Printed at:</strong> {{ $printed_at }}<br>
        <strong>Total serials:</strong> {{ $serial_numbers->count() }}
    </div>

    @foreach($grouped_by_location as $location_name => $rows)
        <div class="loc-title">Location: {{ $location_name }} ({{ $rows->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Serial Number</th>
                    <th>Status</th>
                    <th>Sold Invoice ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $row->product_name }} @if(!empty($row->variation_name)) - {{ $row->variation_name }} @endif</td>
                        <td>{{ $row->serial_number }}</td>
                        <td>{{ ucfirst($row->status) }}</td>
                        <td>{{ $row->sold_transaction_id }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
