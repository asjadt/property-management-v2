<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessDefault extends Model
{
    use HasFactory;
    protected $fillable = [
            'entity_type',
            'entity_id',
            'business_owner_id'
    ];
 

}
