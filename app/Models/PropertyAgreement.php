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
        'landlord_sign_date',
        'agency_sign_date',
        'rent_due_day',
        'payment_arrangement',
        'cheque_payable_to',
        'agent_commission',
        'management_fee',
        'inventory_charges',
        'terms_conditions',
        'legal_representative',
        'min_price',
        'max_price',
        'agency_type',
        'type',
        "files",
        "landlord_sign_images",
        "agency_sign_images"



    ];

    protected $casts = [
        'files' => 'array',
        "landlord_sign_images"=> 'array',
        "agency_sign_images"=> 'array',
    ];

    // Relationships
    public function landlords() {
    return $this->belongsToMany(Landlord::class, 'property_agreement_landlords', 'property_agreement_id', 'landlord_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

}
