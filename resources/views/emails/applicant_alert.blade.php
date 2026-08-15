<!DOCTYPE html>
<html>
<head>
    <title>Applicant Expiry Alert</title>
</head>
<body>
    <h2>Applicant Expiry Alert</h2>
    
    <p>This is a reminder that an applicant's profile is scheduled to expire soon.</p>
    
    <ul>
        <li><strong>Applicant Name:</strong> {{ $applicant->customer_name ?? 'N/A' }}</li>
        <li><strong>Applicant Phone:</strong> {{ $applicant->customer_phone ?? 'N/A' }}</li>
        <li><strong>Applicant Email:</strong> {{ $applicant->email ?? 'N/A' }}</li>
        <li><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($applicant->expiry_date)->format('M d, Y') }}</li>
    </ul>

    <p>Please review the applicant profile and take any necessary actions.</p>

    <br>
    <p>Thank you,</p>
    <p>Property Management System</p>
</body>
</html>
