<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'property_id',
        'start_date',
        'end_date',
        'payment_arrangement',
        'cheque_payable_to',
        'agent_commision',
        'management_fee',
        'inventory_charges',
        'terms_conditions',
    ];

    // Relationships
    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
