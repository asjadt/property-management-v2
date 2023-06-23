<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'image',
        'address',
        'country',
        'city',
        'postcode',
        "lat",
        "long",
        'type',
        'size',
        'amenities',
        'reference_no',
        'landlord_id',
        "created_by",
        'is_active',
    ];
    public function property_tenants() {
        return $this->hasMany(PropertyTenant::class,'property_id','id');
    }
}