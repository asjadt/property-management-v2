<!DOCTYPE html>
<html>
<head>
    <title>Tenancy Agreement Alert</title>
</head>
<body>
    <h2>Tenancy Agreement Expiry Alert</h2>
    
    <p>This is a reminder that a tenancy agreement is scheduled to expire soon.</p>
    
    <ul>
        <li><strong>Property Address:</strong> {{ $agreement->property->address ?? 'N/A' }}</li>
        <li><strong>Date of Moving:</strong> {{ $agreement->date_of_moving ?? 'N/A' }}</li>
        <li><strong>Agreed Rent:</strong> £{{ $agreement->agreed_rent ?? 'N/A' }}</li>
        <li><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($agreement->tenant_contact_expired_date)->format('M d, Y') }}</li>
    </ul>

    <p>Please review the agreement and take any necessary actions regarding renewal or termination.</p>

    <br>
    <p>Thank you,</p>
    <p>Property Management System</p>
</body>
</html>
