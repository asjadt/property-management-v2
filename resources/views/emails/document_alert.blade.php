<!DOCTYPE html>
<html>
<head>
    <title>Document Expiry Alerts</title>
</head>
<body>
    <h1>Document Expiry Alerts</h1>

    @foreach($document_report as $type => $report)
        <h3>{{ $type }}</h3>
        <ul>
            <li>Total Documents: {{ $report['total_data'] }}</li>
            <li>Expired Documents: {{ $report['total_expired'] }}</li>
            <li>Expiring Today: {{ $report['today_expiry'] }}</li>
            <li>Expiring in 15 Days: {{ $report['expires_in_15_days'] }}</li>
            <li>Expiring in 30 Days: {{ $report['expires_in_30_days'] }}</li>
        </ul>
    @endforeach
</body>
</html>
