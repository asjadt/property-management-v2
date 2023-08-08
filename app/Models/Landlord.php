<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Landlord extends Model
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
        "created_by",
         'is_active'
    ];


    public function properties() {
        return $this->hasMany(Property::class,'landlord_id','id');
    }
}
