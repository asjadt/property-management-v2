<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyAgreement extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'property_id',
        'start_date',
        'end_date',
        'payment_arrangement',
        'cheque_payable_to',
        'agent_commission',
        'management_fee',
        'inventory_charges',
        'terms_conditions',
        'legal_representative',
        'min_price',
        'max_price',
        'agency_type'

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
