<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyLandlord extends Model
{
    use HasFactory;

    protected $fillable = [

        'property_id',
        'landlord_id'

    ];

    public function landlords() {
        return $this->hasOne(Tenant::class,'id','landlord_id');
    }



}
