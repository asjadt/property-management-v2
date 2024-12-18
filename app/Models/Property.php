<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Property extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'image',
        'images',
        'address',
        'country',
        'city',
        'postcode',
        "town",
        "lat",
        "long",
        'type',
        'reference_no',
        'landlord_id',
        "created_by",
        'is_active',
        'date_of_instruction', // Added field
        'howDetached',          // Added field
        "no_of_beds",
        "no_of_baths",
        "is_garden",
        'propertyFloor',        // Added field
        'category',
        'min_price',            // Added field
        'max_price',            // Added field
        'purpose',              // Added field
        'property_door_no',     // Added field
        'property_road',        // Added field
        'county',               // Added field
    ];
    protected $casts = [
        'images' => 'array',
    ];

    public function documents()
    {
        return $this->hasMany(PropertyDocument::class);
    }


    public function property_tenants() {
        return $this->belongsToMany(Tenant::class, 'property_tenants', 'property_id', 'tenant_id');
    }

    public function landlord() {
        return $this->hasOne(Landlord::class,'id','landlord_id');
    }

    public function repairs() {
        return $this->hasMany(Repair::class,'property_id','id');
    }
    public function invoices() {
        return $this->hasMany(Invoice::class,'property_id','id')
        ->select('*', DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    '));
    }

}
