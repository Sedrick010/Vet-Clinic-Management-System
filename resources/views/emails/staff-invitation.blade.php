<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .credentials {
            background-color: #f9fafb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4f46e5;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to {{ $clinic->name }}</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $staff->name }},</p>
        
        <p>You have been invited to join the {{ $clinic->name }} team as a <strong>{{ ucfirst($staff->role) }}</strong>.</p>
        
        <p>You can now access our clinic management system using the following credentials:</p>
        
        <div class="credentials">
            <p><strong>Login URL:</strong> <a href="{{ parse_url(config('app.url'), PHP_URL_SCHEME) }}://{{ $clinic->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}">{{ $clinic->name }} Portal</a></p>
            <p><strong>Email:</strong> {{ $staff->email }}</p>
            <p><strong>Password:</strong> {{ $password }}</p>
        </div>
        
        <p><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
        
        <p>If you have any questions or need assistance, please contact the clinic administrator.</p>
        
        <a href="{{ parse_url(config('app.url'), PHP_URL_SCHEME) }}://{{ $clinic->subdomain }}.{{ parse_url(config('app.url'), PHP_URL_HOST) }}" class="button">Login Now</a>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply.</p>
        <p>&copy; {{ date('Y') }} {{ $clinic->name }}. All rights reserved.</p>
    </div>
</body>
</html> 