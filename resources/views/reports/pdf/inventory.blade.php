<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $clinic->name }} - Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #fb6340;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #fb6340;
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        .subheading {
            color: #777;
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .report-meta {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .meta-item {
            margin-bottom: 5px;
        }
        .meta-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        .stat-card {
            width: 48%;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .stat-title {
            font-size: 14px;
            color: #777;
            margin: 0 0 5px 0;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #fb6340;
            margin: 0;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .inventory-table th, .inventory-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .inventory-table th {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
        }
        .badge-low {
            background-color: #fb6340;
        }
        .badge-out {
            background-color: #f5365c;
        }
        .badge-expiring {
            background-color: #11cdef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $clinic->name }}</h1>
            <p class="subheading">Inventory Report</p>
        </div>
        
        <div class="report-meta">
            <div class="meta-item">
                <span class="meta-label">Report Date:</span>
                <span>{{ date('F d, Y') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Generated At:</span>
                <span>{{ date('h:i A') }}</span>
            </div>
        </div>
        
        <h2>Inventory Summary</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Items</p>
                <p class="stat-value">{{ number_format($data['total_items'] ?? 0) }}</p>
                @if(isset($data['active_items']) && isset($data['inactive_items']))
                <p style="margin-top: 5px; font-size: 14px;">
                    <span style="color: #2dce89;">{{ number_format($data['active_items'] ?? 0) }}</span> active, 
                    <span style="color: #8898aa;">{{ number_format($data['inactive_items'] ?? 0) }}</span> inactive
                </p>
                @endif
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Low Stock Items</p>
                <p class="stat-value">{{ count($data['low_stock_items'] ?? []) }}</p>
                @if(isset($data['low_stock_items']))
                <p style="margin-top: 5px; font-size: 14px;">
                    {{ count(array_filter($data['low_stock_items'] ?? [], function($item) { return $item->quantity <= 3; })) }} critical level
                </p>
                @endif
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Out of Stock Items</p>
                <p class="stat-value">{{ count($data['out_of_stock_items'] ?? []) }}</p>
                <p style="margin-top: 5px; font-size: 14px; color: {{ count($data['out_of_stock_items'] ?? []) > 0 ? '#f5365c' : '#2dce89' }};">
                    {{ count($data['out_of_stock_items'] ?? []) > 0 ? 'Needs restocking' : 'All items in stock' }}
                </p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Expiring Soon</p>
                <p class="stat-value">{{ count($data['expiring_soon_items'] ?? []) }}</p>
                <p style="margin-top: 5px; font-size: 14px;">
                    Within 30 days
                    @if(isset($data['expiring_next_week']))
                    ({{ $data['expiring_next_week'] ?? 0 }} within 7 days)
                    @endif
                </p>
            </div>
        </div>
        
        @if(isset($data['total_inventory_value']) || isset($data['avg_item_value']))
        <h2>Inventory Value</h2>
        
        <div class="stats-container">
            @if(isset($data['total_inventory_value']))
            <div class="stat-card">
                <p class="stat-title">Total Inventory Value</p>
                <p class="stat-value">${{ number_format($data['total_inventory_value'] ?? 0, 2) }}</p>
            </div>
            @endif
            
            @if(isset($data['avg_item_value']))
            <div class="stat-card">
                <p class="stat-title">Average Item Value</p>
                <p class="stat-value">${{ number_format($data['avg_item_value'] ?? 0, 2) }}</p>
            </div>
            @endif
            
            @if(isset($data['low_stock_value']))
            <div class="stat-card">
                <p class="stat-title">Low Stock Value</p>
                <p class="stat-value">${{ number_format($data['low_stock_value'] ?? 0, 2) }}</p>
            </div>
            @endif
        </div>
        @endif
        
        <h2>Critical Inventory Items</h2>
        
        @if((isset($data['low_stock_items']) && count($data['low_stock_items']) > 0) || (isset($data['out_of_stock_items']) && count($data['out_of_stock_items']) > 0))
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Status</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($data['low_stock_items']))
                    @foreach($data['low_stock_items'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td><span class="status-badge badge-low">Low Stock</span></td>
                        <td>{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                @endif
                
                @if(isset($data['out_of_stock_items']))
                    @foreach($data['out_of_stock_items'] as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td><span class="status-badge badge-out">Out of Stock</span></td>
                        <td>0</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        @else
        <p>All inventory items are at adequate stock levels.</p>
        @endif
        
        <h2>Items Expiring Soon</h2>
        
        @if(isset($data['expiring_soon_items']) && count($data['expiring_soon_items']) > 0)
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Expiry Date</th>
                    <th>Days Remaining</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['expiring_soon_items'] as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->expiry_date)->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->expiry_date)->diffInDays(\Carbon\Carbon::now()) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No items are expiring within the next 30 days.</p>
        @endif
        
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $clinic->name }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html> 