<!DOCTYPE html>
<html>
<head>
    <title>Property Document Alert</title>
</head>
<body>
    <h2>Property Document Expiry Alert</h2>
    
    <p>This is a reminder that a property document is scheduled to expire soon.</p>
    
    <ul>
        <li><strong>Property Address:</strong> {{ $document->property->address ?? 'N/A' }}</li>
        <li><strong>Document Type ID:</strong> {{ $document->document_type_id }}</li>
        <li><strong>Description:</strong> {{ $document->description ?? 'None' }}</li>
        <li><strong>Expiry Date (Gas End Date):</strong> {{ \Carbon\Carbon::parse($document->gas_end_date)->format('M d, Y') }}</li>
    </ul>

    <p>Please review and upload any renewed documents.</p>

    <br>
    <p>Thank you,</p>
    <p>Property Management System</p>
</body>
</html>
