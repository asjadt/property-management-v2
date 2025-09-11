<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandlordRentPayable extends Model
{
    use HasFactory;

  // Fillable fields
    protected $fillable = [
        'generated_id',
        'payment_method',
        "landlord_id",
        'item_description',
        'total_amount',
        'create_date',
        'status',
        'is_active',
        'created_by',
    ];

    // Relationships

    // Creator (user)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Related rents
    public function payable_rents()
    {
        return $this->hasMany(LandlordPayableRent::class, 'landlord_rent_payable_id', 'id');
    }

    public function rents()
    {
        return $this->belongsToMany(Rent::class,"landlord_payable_rents", 'landlord_rent_payable_id', 'rent_id');
    }


    // Rent adjustments
    public function rent_adjustments()
    {
        return $this->hasMany(RentAdjustment::class, 'landlord_rent_payable_id', 'id');
    }

    
}
