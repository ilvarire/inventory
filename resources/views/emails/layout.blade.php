<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notification')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }

        .email-header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }

        .email-header p {
            color: #e0e7ff;
            font-size: 14px;
            margin: 5px 0 0 0;
        }

        .email-body {
            padding: 30px 20px;
        }

        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }

        .alert-success {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 10px;
            font-weight: 600;
            color: #666666;
            width: 40%;
            border-bottom: 1px solid #eeeeee;
        }

        .info-value {
            display: table-cell;
            padding: 10px;
            color: #333333;
            border-bottom: 1px solid #eeeeee;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #667eea;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
            text-align: center;
        }

        .button-primary {
            background-color: #667eea;
        }

        .button-success {
            background-color: #28a745;
        }

        .button-danger {
            background-color: #dc3545;
        }

        .button:hover {
            opacity: 0.9;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background-color: #28a745;
            transition: width 0.3s ease;
        }

        .progress-fill.warning {
            background-color: #ffc107;
        }

        .progress-fill.danger {
            background-color: #dc3545;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-critical {
            background-color: #dc3545;
            color: #ffffff;
        }

        .badge-high {
            background-color: #fd7e14;
            color: #ffffff;
        }

        .badge-medium {
            background-color: #ffc107;
            color: #333333;
        }

        .badge-low {
            background-color: #28a745;
            color: #ffffff;
        }

        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 20px 15px;
            }

            .button {
                display: block;
                margin: 10px 0;
            }

            .info-label,
            .info-value {
                display: block;
                width: 100%;
            }

            .info-label {
                padding-bottom: 5px;
                border-bottom: none;
            }

            .info-value {
                padding-top: 0;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>@yield('header-title', 'Inventory Management System')</h1>
            <p>@yield('header-subtitle', 'Notification Alert')</p>
        </div>

        <div class="email-body">
            @yield('content')
        </div>

        <div class="email-footer">
            <p>
                <strong>{{ config('app.name') }}</strong><br>
                This is an automated notification. Please do not reply to this email.<br>
                <a href="{{ config('app.url') }}">Visit Dashboard</a> |
                <a href="{{ config('app.url') }}/settings/notifications">Notification Settings</a>
            </p>
            <p style="margin-top: 10px; color: #999999;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>

</html>