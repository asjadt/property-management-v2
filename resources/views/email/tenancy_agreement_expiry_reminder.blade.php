<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>
    <h2>{{ $title }}</h2>
    <p>Dear {{ $business->name }},</p>
    <p>{{ $message_desc }}</p>

    <h3>🏠 Property Details:</h3>
    <ul>
        <li><strong>Property Name:</strong> {{ $property->name }}</li>
        <li><strong>Address:</strong> {{ $property->address }}, {{ $property->city }}, {{ $property->country }}</li>
        <li><strong>Reference No:</strong> {{ $property->reference_no }}</li>
        <li><strong>Price:</strong> £{{ number_format($property->price, 2) }}</li>
    </ul>

    <h3>📄 Tenancy Agreement Details:</h3>
    <ul>
        <li><strong>ID:</strong> {{ $agreement->id }}</li>
        <li><strong>Tenant Name(s):</strong> {{ $agreement->tenants->pluck('name')->join(', ') }}</li>
        <li><strong>Expiry Date:</strong> {{ $agreement->tenant_contact_expired_date ?? 'Ongoing / Periodic' }}</li>
        <li><strong>Agreed Rent:</strong> £{{ number_format($agreement->agreed_rent, 2) }}</li>
    </ul>

    <p>You can review and update the agreement by clicking the link below:</p>
    <p><a href="{{ url('tenancy-agreements/' . $agreement->id) }}">View Tenancy Agreement</a></p>

    <p>Best regards,</p>
    <p><strong>{{ config('app.name') }}</strong></p>
</body>
</html>
