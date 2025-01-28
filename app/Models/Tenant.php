<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'first_Name',
        'last_Name',
        'phone',
        'image',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postcode',
        "lat",
        "long",
        'email',
        'files',
        "created_by",
         'is_active'
    ];

    protected $casts = [
        'files' => 'array',
    ];



    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_tenants', 'tenant_id', 'property_id');
    }


    public function tenancy_agreements()
    {
        return $this->belongsToMany(TenancyAgreement::class, 'agreement_tenants', 'tenant_id',"tenancy_agreement_id");
    }



}
