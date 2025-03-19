<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'property_id',
        'repair_category_id',
        'item_description',
        'status',
        'receipt',
        'price',
        'create_date',
        "created_by"

    ];

    public function repair_category() {
        return $this->hasOne(RepairCategory::class,'id','repair_category_id');
    }

    public function property() {
        return $this->hasOne(Property::class,'id','property_id');
    }
    public function repair_images() {
        return $this->hasMany(RepairImage::class,'repair_id','id');
    }
    protected $casts = [
        'receipt' => 'array',
    ];
}
