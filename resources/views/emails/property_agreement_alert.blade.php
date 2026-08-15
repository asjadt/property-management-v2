<!DOCTYPE html>
<html>
<head>
    <title>Property Agreement Alert</title>
</head>
<body>
    <h2>Property Agreement Expiry Alert</h2>
    
    <p>This is a reminder that a property agreement is scheduled to expire soon.</p>
    
    <ul>
        <li><strong>Property Address:</strong> {{ $agreement->property->address ?? 'N/A' }}</li>
        <li><strong>Start Date:</strong> {{ $agreement->start_date ?? 'N/A' }}</li>
        <li><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($agreement->end_date)->format('M d, Y') }}</li>
    </ul>

    <p>Please review the agreement and take any necessary actions regarding renewal or termination.</p>

    <br>
    <p>Thank you,</p>
    <p>Property Management System</p>
</body>
</html>
