<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Item Alert</title>
</head>
<body>
    <h2>Maintenance Item Reminder Alert</h2>
    
    <p>This is a reminder that a maintenance item is scheduled for follow up soon.</p>
    
    <ul>
        <li><strong>Inspection Date:</strong> {{ $item->inspection->date ?? 'N/A' }}</li>
        <li><strong>Property Address:</strong> {{ $item->inspection->property->address ?? 'N/A' }}</li>
        <li><strong>Status:</strong> {{ $item->status }}</li>
        <li><strong>Comment:</strong> {{ $item->comment ?? 'None' }}</li>
        <li><strong>Follow-up Date:</strong> {{ \Carbon\Carbon::parse($item->next_follow_up_date)->format('M d, Y') }}</li>
    </ul>

    <p>Please take any necessary actions regarding this maintenance item.</p>

    <br>
    <p>Thank you,</p>
    <p>Property Management System</p>
</body>
</html>
