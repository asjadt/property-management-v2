<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'repair_category',
        'item_description',
        'receipt',
        'price',
        'create_date',

    ];
    public function property() {
        return $this->hasMany(Property::class,'id','property_id');
    }
    public function repair_images() {
        return $this->hasMany(RepairImage::class,'repair_id','id');
    }

}
