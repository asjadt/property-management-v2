<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Matching Properties</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f4f4f4; padding: 20px; text-align: center; border-radius: 5px; }
        .property-card { border: 1px solid #ddd; margin: 20px 0; border-radius: 5px; overflow: hidden; }
        .property-image { width: 100%; height: 200px; background-color: #eee; background-size: cover; background-position: center; }
        .property-details { padding: 15px; }
        .property-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #2c3e50; }
        .property-price { font-size: 16px; color: #27ae60; font-weight: bold; margin-bottom: 5px; }
        .property-meta { font-size: 14px; color: #7f8c8d; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Hello {{ $applicant->customer_name }},</h2>
            <p>We found {{ $properties->count() }} {{ $properties->count() == 1 ? 'property' : 'properties' }} that perfectly match your requirements!</p>
        </div>

        @foreach($properties as $property)
            <div class="property-card">
                @if($property->image)
                    <div class="property-image" style="background-image: url('{{ $property->image }}')"></div>
                @else
                    <div class="property-image" style="background-image: url('https://via.placeholder.com/600x400?text=No+Image+Available')"></div>
                @endif
                <div class="property-details">
                    <div class="property-title">{{ $property->name ?: $property->address }}</div>
                    <div class="property-price">£{{ number_format($property->price, 2) }}</div>
                    <div class="property-meta">
                        {{ $property->no_of_beds }} Beds &bull; {{ $property->no_of_baths }} Baths &bull; {{ str_replace('_', ' ', Str::title($property->type)) }}
                    </div>
                    <div class="property-meta" style="margin-top: 5px;">
                        Location: {{ implode(', ', array_filter([$property->city, $property->county, $property->postcode])) }}
                    </div>
                </div>
            </div>
        @endforeach

        <div class="footer">
            <p>Thank you for using our services.</p>
        </div>
    </div>
</body>
</html>
