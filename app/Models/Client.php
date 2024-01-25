<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'first_Name',
        'last_Name',
        "company_name",
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

}
