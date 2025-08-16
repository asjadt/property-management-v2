<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordPayableRent extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_rent_payable_id',
        'rent_id',
    ];

    // Relationships
    public function landlord_rent_payable()
    {
        return $this->belongsTo(LandlordRentPayable::class);
    }

    public function rent()
    {
        return $this->belongsTo(Rent::class);
    }
}