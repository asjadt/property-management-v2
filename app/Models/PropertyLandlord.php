<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyLandlord extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'landlord_id',
    ];

    /**
     * The landlord on this property-landlord pivot row.
     * FIXED: was incorrectly pointing to Tenant::class.
     */
    public function landlord()
    {
        return $this->belongsTo(Landlord::class, 'landlord_id', 'id');
    }

    /**
     * The property on this pivot row.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }
}
