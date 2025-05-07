<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $clinic->name }} - Client & Patient Report</title>
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
            border-bottom: 2px solid #2dce89;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #2dce89;
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
            color: #2dce89;
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
        .chart-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
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
            <p class="subheading">Client & Patient Report</p>
        </div>
        
        <div class="report-meta">
            <div class="meta-item">
                <span class="meta-label">Report Period:</span>
                <span>{{ $data['period'] ?? date('M d, Y') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Generated On:</span>
                <span>{{ date('F d, Y, h:i A') }}</span>
            </div>
        </div>
        
        <h2>Client Statistics</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Clients</p>
                <p class="stat-value">{{ number_format($data['total_clients'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">New Clients</p>
                <p class="stat-value">{{ number_format($data['new_clients'] ?? 0) }}</p>
                @if(isset($data['total_clients']) && $data['total_clients'] > 0 && isset($data['new_clients']))
                <p style="margin-top: 5px; font-size: 14px; color: #2dce89;">
                    {{ round(($data['new_clients'] / $data['total_clients']) * 100, 1) }}% of total
                </p>
                @endif
            </div>
        </div>
        
        <h2>Patient Statistics</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Patients (Pets)</p>
                <p class="stat-value">{{ number_format($data['total_pets'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">New Patients</p>
                <p class="stat-value">{{ number_format($data['new_pets'] ?? 0) }}</p>
                @if(isset($data['total_pets']) && $data['total_pets'] > 0 && isset($data['new_pets']))
                <p style="margin-top: 5px; font-size: 14px; color: #2dce89;">
                    {{ round(($data['new_pets'] / $data['total_pets']) * 100, 1) }}% of total
                </p>
                @endif
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Clients with Multiple Pets</p>
                <p class="stat-value">{{ number_format($data['clients_with_multiple_pets'] ?? 0) }}</p>
                @if(isset($data['total_clients']) && $data['total_clients'] > 0 && isset($data['clients_with_multiple_pets']))
                <p style="margin-top: 5px; font-size: 14px; color: #2dce89;">
                    {{ round(($data['clients_with_multiple_pets'] / $data['total_clients']) * 100, 1) }}% of total clients
                </p>
                @endif
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Pets per Client Ratio</p>
                <p class="stat-value">
                    @if(isset($data['total_clients']) && $data['total_clients'] > 0 && isset($data['total_pets']))
                        {{ round($data['total_pets'] / $data['total_clients'], 2) }}
                    @else
                        0
                    @endif
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $clinic->name }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html> 