<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_rent_payable_id',
        'amount',
        'description',
    ];

    // Relationships
    public function landlord_rent_payable()
    {
        return $this->belongsTo(LandlordRentPayable::class);
    }
}