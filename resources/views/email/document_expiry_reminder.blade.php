<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <h2>{{ $title }}</h2>

    <p>Dear {{ $business->name }},</p>

    <p>{{ $message }}</p>

    <h3>📄 Document Details:</h3>
    <ul>
        <li><strong>ID:</strong> {{ $document->id }}</li>
        <li><strong>Description:</strong> {{ $document->description }}</li>
        <li><strong>Expiry Date:</strong> {{ $document->gas_end_date }}</li>
        <li><strong>Document Type:</strong> {{ $document->document_type_id }}</li>
    </ul>

    <h3>🏠 Property Details:</h3>
    <ul>
        <li><strong>Property Name:</strong> {{ $property->name }}</li>
        <li><strong>Address:</strong> {{ $property->address }}, {{ $property->city }}, {{ $property->country }}</li>
        <li><strong>Reference No:</strong> {{ $property->reference_no }}</li>
        <li><strong>Price:</strong> ${{ number_format($property->price, 2) }}</li>
    </ul>

    <p>You can review and update the document by clicking the link below:</p>
    <p><a href="{{ url('documents/' . $document->id) }}">View Document</a></p>

    <p>Best regards,</p>
    <p><strong>{{ config('app.name') }}</strong></p>
</body>
</html>
