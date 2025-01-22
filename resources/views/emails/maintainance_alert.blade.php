<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Expiry Alerts</title>
</head>
<body>
    <h1>Maintenance Expiry Alerts</h1>

    <ul>
        <li>Total Properties: {{ $maintainance_report['total_data'] }}</li>
        <li>Expired Maintenance: {{ $maintainance_report['total_expired'] }}</li>
        <li>Maintenance Due Today: {{ $maintainance_report['today_expiry'] }}</li>
        <li>Maintenance Due in 15 Days: {{ $maintainance_report['expires_in_15_days'] }}</li>
        <li>Maintenance Due in 30 Days: {{ $maintainance_report['expires_in_30_days'] }}</li>
    </ul>
</body>
</html>
