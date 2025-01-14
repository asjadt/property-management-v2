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
        'is_active',
        'date_of_instruction',
        'howDetached',
        "no_of_beds",
        "no_of_baths",
        "is_garden",
        'propertyFloor',
        'category',
        'min_price',
        'max_price',
        'purpose',
        'property_door_no',
        'property_road',
        'is_dss',
        'county',
        "created_by",
    ];
    protected $casts = [
        'images' => 'array',
    ];

    public function documents()
    {
        return $this->hasMany(PropertyDocument::class);
    }


    public function inspections()
    {
        return $this->hasMany(TenantInspection::class,"property_id","id");
    }


    public function property_tenants() {
        return $this->belongsToMany(Tenant::class, 'property_tenants', 'property_id', 'tenant_id');
    }


    public function property_landlords() {
        return $this->belongsToMany(Landlord::class, 'property_landlords', 'property_id', 'landlord_id');
    }


    public function maintenance_item_types() {
        return $this->belongsToMany(MaintenanceItemType::class, 'maintenance_item_properties',
        'property_id',
        'maintenance_item_type_id'
    );





    }





    public function landlord() {
        return $this->hasOne(Landlord::class,'id','landlord_id')->withTrashed();
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
