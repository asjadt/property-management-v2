@php
        return;
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
</head>
<body>

    @php
        return;
    @endphp
    <h2>{{ $title }}</h2>

    <p>Dear {{ $business->name }},</p>

    <p>{{ $message_desc }}</p>

    <h3>📝 Inspection Details:</h3>
    <ul>
        <li><strong>Inspection ID:</strong> {{ $inspection->id }}</li>
        <li><strong>Inspected By:</strong> {{ $inspection->inspected_by }}</li>
        <li><strong>Next Inspection Date:</strong> {{ \Carbon\Carbon::parse($inspection->next_inspection_date)->format('d-m-Y') }}</li>
        <li><strong>Inspection Duration:</strong> {{ $inspection->inspection_duration }}</li>
        <li><strong>Comments:</strong> {{ $inspection->comments }}</li>
    </ul>

    <h3>🏠 Property Details:</h3>
    <ul>
        <li><strong>Property Name:</strong> {{ $property->name }}</li>
        <li><strong>Address:</strong> {{ $property->address }}, {{ $property->city }}, {{ $property->country }}</li>
        <li><strong>Reference No:</strong> {{ $property->reference_no }}</li>
        <li><strong>Price:</strong> ${{ number_format($property->price, 2) }}</li>
    </ul>

    <p>You can review and update the inspection by clicking the link below:</p>
    <p><a href="{{ url('inspections/' . $inspection->id) }}">View Inspection</a></p>

    <p>Best regards,</p>
    <p><strong>{{ config('app.name') }}</strong></p>
</body>
</html>
