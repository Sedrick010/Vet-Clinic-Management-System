<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $clinic->name }} - Appointment Analytics Report</title>
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
            border-bottom: 2px solid #11cdef;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #11cdef;
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
            color: #11cdef;
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
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .status-table th, .status-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .status-table th {
            background-color: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
        }
        .badge-completed {
            background-color: #2dce89;
        }
        .badge-scheduled {
            background-color: #11cdef;
        }
        .badge-cancelled {
            background-color: #f5365c;
        }
        .badge-noshow {
            background-color: #fb6340;
        }
        .badge-rescheduled {
            background-color: #5e72e4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $clinic->name }}</h1>
            <p class="subheading">Appointment Analytics Report</p>
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
        
        <h2>Appointment Statistics</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Appointments</p>
                <p class="stat-value">{{ number_format($data['total_appointments'] ?? 0) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Period Appointments</p>
                <p class="stat-value">{{ number_format($data['period_appointments'] ?? 0) }}</p>
                @if(isset($data['total_appointments']) && $data['total_appointments'] > 0 && isset($data['period_appointments']))
                <p style="margin-top: 5px; font-size: 14px; color: #11cdef;">
                    {{ round(($data['period_appointments'] / $data['total_appointments']) * 100, 1) }}% of total
                </p>
                @endif
            </div>
        </div>
        
        <h2>Status Distribution</h2>
        
        @if(isset($data['status_distribution']) && !empty($data['status_distribution']))
        <table class="status-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusColors = [
                        'completed' => 'completed',
                        'scheduled' => 'scheduled',
                        'cancelled' => 'cancelled',
                        'no-show' => 'noshow',
                        'rescheduled' => 'rescheduled'
                    ];
                    $totalCount = array_sum($data['status_distribution']);
                @endphp
                
                @foreach($data['status_distribution'] as $status => $count)
                <tr>
                    <td>
                        <span class="status-badge badge-{{ $statusColors[$status] ?? 'secondary' }}">{{ ucfirst($status) }}</span>
                    </td>
                    <td>{{ number_format($count) }}</td>
                    <td>{{ round(($count / $totalCount) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No status distribution data available for this period.</p>
        @endif
        
        <h2>Appointment Types</h2>
        
        @if(isset($data['type_distribution']) && !empty($data['type_distribution']))
        <table class="status-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalTypes = array_sum($data['type_distribution']);
                @endphp
                
                @foreach($data['type_distribution'] as $type => $count)
                <tr>
                    <td>{{ ucfirst($type) }}</td>
                    <td>{{ number_format($count) }}</td>
                    <td>{{ round(($count / $totalTypes) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No appointment type distribution data available for this period.</p>
        @endif
        
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $clinic->name }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html> 