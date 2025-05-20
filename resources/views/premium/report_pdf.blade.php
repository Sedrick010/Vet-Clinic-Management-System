<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $clinic->name }} - {{ ucfirst($report_type) }} Report</title>
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
            border-bottom: 2px solid #3490dc;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #3490dc;
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
            color: #3490dc;
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
            <p class="subheading">{{ ucfirst($report_type) }} Performance Report</p>
        </div>
        
        <div class="report-meta">
            <div class="meta-item">
                <span class="meta-label">Report Period:</span>
                <span>{{ $period }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Generated On:</span>
                <span>{{ $generated_at }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Report Type:</span>
                <span>{{ ucfirst($report_type) }}</span>
            </div>
        </div>
        
        <h2>Performance Summary</h2>
        
        <div class="stats-container">
            <div class="stat-card">
                <p class="stat-title">Total Patients (Pets)</p>
                <p class="stat-value">{{ number_format($total_patients) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Visits During Period</p>
                <p class="stat-value">{{ number_format($visits) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">Total Clients</p>
                <p class="stat-value">{{ number_format($total_clients) }}</p>
            </div>
            
            <div class="stat-card">
                <p class="stat-title">New Clients in Period</p>
                <p class="stat-value">{{ number_format($new_clients) }}</p>
            </div>
        </div>
        
        <h2>Analysis</h2>
        
        <p>During this period, your clinic had <strong>{{ number_format($visits) }}</strong> visits from a total of <strong>{{ number_format($total_patients) }}</strong> patients.</p>
        
        @if($new_clients > 0)
        <p>You acquired <strong>{{ number_format($new_clients) }}</strong> new clients during this period, bringing your total client base to <strong>{{ number_format($total_clients) }}</strong>.</p>
        @else
        <p>No new clients were acquired during this period. Your total client base stands at <strong>{{ number_format($total_clients) }}</strong>.</p>
        @endif
        
        <h2>Recommendations</h2>
        
        <ul>
            @if($visits > 0 && $new_clients > 0)
            <li>Your clinic is showing healthy activity with both new and returning clients.</li>
            @elseif($visits > 0 && $new_clients == 0)
            <li>Consider implementing marketing strategies to attract new clients.</li>
            @elseif($visits == 0 && $new_clients > 0)
            <li>You're acquiring new clients, but follow-up visits may need attention.</li>
            @else
            <li>Review your marketing and client engagement strategies.</li>
            @endif
            
            <li>Regularly check in with clients through email newsletters or social media updates.</li>
            <li>Schedule automated reminders for check-ups and vaccinations to maintain client relationships.</li>
            <li>Consider implementing a referral program to encourage existing clients to refer new pet owners to your clinic.</li>
        </ul>
        
        <div class="footer">
            <p>This report was automatically generated by {{ config('app.name') }} Premium Analytics.</p>
            <p>© {{ date('Y') }} {{ $clinic->name }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html> 