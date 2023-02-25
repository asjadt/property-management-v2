<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSubService extends Model
{
    use HasFactory;

    protected $fillable = [
        "booking_id",
        "sub_service_id",
    ];
    public function sub_service(){
        return $this->belongsTo(SubService::class,'sub_service_id', 'id');
    }

}
