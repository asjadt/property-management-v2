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
        'is_active',
        'current_status',
        'date_of_instruction',
        'howDetached',
        "no_of_beds",
        "no_of_baths",
        "is_garden",
        'propertyFloor',
        'category',
        'price',
        'purpose',
        'property_door_no',
        'property_road',
        'is_dss',
        'county',
        "created_by",

        // 2ND MIGRATION
        "min_price",
        "max_price",

        // NEW LOOKUP COLUMNS
        "property_type_id",
        "bed_id",
        "bath_id",
    ];

    // ACCESSORS AND MUTATORS FOR MIGRATION ABSTRACTION

    public function setPropertyTypeIdAttribute($value)
    {
        $this->attributes['property_type_id'] = $value;
        if ($value) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('property_types_id_to_title', fn() => \App\Models\PropertyType::pluck('title', 'id')->toArray());
            if (isset($map[$value])) {
                $this->attributes['type'] = str_replace(' ', '_', strtolower($map[$value]));
            }
        }
    }

    public function setBedIdAttribute($value)
    {
        $this->attributes['bed_id'] = $value;
        if ($value) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('beds_id_to_title', fn() => \App\Models\Bed::pluck('title', 'id')->toArray());
            if (isset($map[$value])) {
                $this->attributes['no_of_beds'] = str_replace('-', '_', strtolower($map[$value]));
            }
        }
    }

    public function setBathIdAttribute($value)
    {
        $this->attributes['bath_id'] = $value;
        if ($value) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('baths_id_to_title', fn() => \App\Models\Bath::pluck('title', 'id')->toArray());
            if (isset($map[$value])) {
                $this->attributes['no_of_baths'] = strtolower($map[$value]);
            }
        }
    }

    public function getPropertyTypeIdAttribute($value)
    {
        if ($value) return $value;
        if (!empty($this->attributes['type'])) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('property_types_title_to_id', fn() => \App\Models\PropertyType::pluck('id', 'title')->toArray());
            $title = ucwords(str_replace('_', ' ', $this->attributes['type']));
            return $map[$title] ?? null;
        }
        return null;
    }

    public function getBedIdAttribute($value)
    {
        if ($value) return $value;
        if (!empty($this->attributes['no_of_beds'])) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('beds_title_to_id', fn() => \App\Models\Bed::pluck('id', 'title')->toArray());
            $title = ucwords(str_replace('_', '-', $this->attributes['no_of_beds']));
            return $map[$title] ?? null;
        }
        return null;
    }

    public function getBathIdAttribute($value)
    {
        if ($value) return $value;
        if (!empty($this->attributes['no_of_baths'])) {
            $map = \Illuminate\Support\Facades\Cache::rememberForever('baths_title_to_id', fn() => \App\Models\Bath::pluck('id', 'title')->toArray());
            $title = ucwords(str_replace('_', '-', $this->attributes['no_of_baths']));
            return $map[$title] ?? null;
        }
        return null;
    }

    protected $casts = [
        'images' => 'array',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, "created_by", "id");  // Returns the latest inspection
    }

    public function documents()
    {
        return $this->hasMany(PropertyDocument::class);
    }

    public function latest_documents()
    {
        return $this->hasMany(PropertyDocument::class)
            ->select('property_documents.*')  // Select all columns of the PropertyDocument model
            ->distinct('document_type_id')    // Ensure only unique document_type_id records are selected
            ->orderByDesc('gas_start_date');  // Specify which column determines the latest record
    }

    public function inspections()
    {
        return $this->hasMany(TenantInspection::class, "property_id", "id");
    }

    public function tenancy_agreements()
    {
        return $this->hasMany(TenancyAgreement::class, "property_id", "id");
    }
    public function latest_tenancy_agreement()
    {
        return $this->hasOne(TenancyAgreement::class, "property_id", "id")

            ->orderByDesc('tenant_contact_expired_date');  // Specify which column determines the latest record
    }

    public function latest_property_agreement()
    {
        return $this->hasOne(PropertyAgreement::class, "property_id", "id")
            ->orderByDesc('end_date');  // Specify which column determines the latest record
    }


    public function latest_inspection()
    {
        return $this->hasOne(TenantInspection::class, "property_id", "id")
            ->orderByDesc("tenant_inspections.date");  // Returns the latest inspection
    }


    public function property_tenants()
    {
        return $this->belongsToMany(Tenant::class, 'property_tenants', 'property_id', 'tenant_id');
    }


    public function property_landlords()
    {
        return $this->belongsToMany(Landlord::class, 'property_landlords', 'property_id', 'landlord_id');
    }


    public function maintenance_item_types()
    {
        return $this->belongsToMany(
            MaintenanceItemType::class,
            'maintenance_item_properties',
            'property_id',
            'maintenance_item_type_id'
        );
    }







    public function status_histories()
    {
        return $this->hasMany(PropertyStatusHistory::class, 'property_id', 'id')->orderByDesc('from_date')->orderByDesc('id');
    }

    public function latest_status_history()
    {
        return $this->hasOne(PropertyStatusHistory::class, 'property_id', 'id')->latestOfMany();
    }


    public function repairs()
    {
        return $this->hasMany(Repair::class, 'property_id', 'id');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'property_id', 'id')
            ->select('*', DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    '));
    }
}
