<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenancyAgreement extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'agreed_rent',
        'security_deposit_hold',
        'rent_payment_option',
        'tenant_contact_duration',
        'date_of_moving',
        'tenant_contact_expired_date',

        'holder_reference_number',
        'holder_entity_id',

        'let_only_agreement_expired_date',

        'rent_due_day',
        'no_of_occupants',
        "tenant_contact_year_duration",
        'renewal_fee',
        'housing_act',
        'let_type',
        'terms_and_conditions',
        'agency_name',
        'landlord_name',
        'agency_witness_name',
        'tenant_witness_name',
        'agency_witness_address',
        'tenant_witness_address',
        'guarantor_name',
        'guarantor_address',
        "tenant_sign_date",
        "agency_sign_date",
        'files',
        "tenant_sign_images",
        "agency_sign_images",



    ];
    protected $casts = [

        'files' => 'array',
        "tenant_sign_images" => "array",
        "agency_sign_images" => "array"

    ];

    public function property()
    {
        return $this->belongsTo(Property::class,"property_id","id");
    }

    public function rent()
    {
        return $this->hasOne(Rent::class,"tenancy_agreement_id","id");
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'agreement_tenants', 'tenancy_agreement_id', 'tenant_id');
    }

}
