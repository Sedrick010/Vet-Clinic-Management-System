<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $clinic->name }} - Dashboard Report</title>
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
            border-bottom: 2px solid #5e72e4;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #5e72e4;
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
            color: #5e72e4;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $clinic->name }}</h1>
            <p class="subheading">Dashboard Summary Report</p>
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
        
        <h2>Performance Summary</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Clients</p>
                <p class="stat-value">{{ number_format($data['total_clients'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Total Patients (Pets)</p>
                <p class="stat-value">{{ number_format($data['total_pets'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Total Appointments</p>
                <p class="stat-value">{{ number_format($data['total_appointments'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Completion Rate</p>
                <p class="stat-value">{{ $data['completion_rate'] ?? 0 }}%</p>
            </div>
        </div>
        
        <h2>Key Metrics</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Monthly Appointments</p>
                <p class="stat-value">{{ number_format($data['monthly_appointments'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Upcoming Appointments</p>
                <p class="stat-value">{{ number_format($data['upcoming_appointments'] ?? 0) }}</p>
            </div>
        </div>
        
        @if(isset($data['low_stock_items']) && count($data['low_stock_items']) > 0)
        <h2>Low Stock Items</h2>
        <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; margin-bottom: 25px;">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th align="left">Item Name</th>
                    <th align="center">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['low_stock_items'] as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td align="center">{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $clinic->name }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html> 