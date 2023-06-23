<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyTenant extends Model
{
    use HasFactory;

    protected $fillable = [

        'property_id',
        'tenant_id'

    ];
    public function property_tenants() {
        return $this->hasOne(Tenant::class,'id','tenant_id');
    }

}
