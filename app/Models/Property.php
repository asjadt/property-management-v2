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

    public function scopePropertyFilters($query, array $filters = [])
    {
        $tableName = $this->getTable();



        if (auth()->check()) {
            $query->where($tableName . ".created_by", auth()->id());
        }

        if (!empty($filters['current_status'])) {
            $query->where($tableName . ".current_status", $filters['current_status']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where($tableName . ".is_active", filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search_key'])) {
            $term = $filters['search_key'];
            $query->where(function ($q) use ($term, $tableName) {
                $q->where($tableName . ".reference_no", "like", "%" . $term . "%")
                  ->orWhere($tableName . ".address", "like", "%" . $term . "%")
                  ->orWhere($tableName . ".type", "like", "%" . $term . "%");
            });
        }

        if (!empty($filters['landlord_ids']) || !empty($filters['landlord_id'])) {
            $landlord_ids = !empty($filters['landlord_ids']) ? explode(',', $filters['landlord_ids']) : explode(',', $filters['landlord_id']);
            $query->whereHas("property_landlords", function ($q) use ($landlord_ids) {
                $q->whereIn("property_landlords.landlord_id", $landlord_ids);
            });
        }

        if (!empty($filters['tenant_ids']) || !empty($filters['tenant_id'])) {
            $tenant_ids = !empty($filters['tenant_ids']) ? explode(',', $filters['tenant_ids']) : explode(',', $filters['tenant_id']);
            $query->whereHas("property_tenants", function ($q) use ($tenant_ids) {
                $q->whereIn("property_tenants.tenant_id", $tenant_ids);
            });
        }

        if (!empty($filters['is_agency_agreement_expired']) || !empty($filters['agency_agreement_expired_in'])) {
            $query->whereHas("latest_property_agreement", function ($subQuery) use ($filters) {
                if (!empty($filters['is_agency_agreement_expired']) && filter_var($filters['is_agency_agreement_expired'], FILTER_VALIDATE_BOOLEAN)) {
                    $subQuery->whereDate('end_date', '<', \Carbon\Carbon::today());
                }
                if (!empty($filters['agency_agreement_expired_in'])) {
                    $expiryDays = $filters['agency_agreement_expired_in'];
                    if (is_numeric($expiryDays) && $expiryDays > 0) {
                        $subQuery->whereDate('end_date', '>', \Carbon\Carbon::today())
                                 ->whereDate('end_date', '<=', \Carbon\Carbon::today()->addDays((int) $expiryDays));
                    }
                }
            });
        }

        if (!empty($filters['is_tenancy_agreement_expired']) || !empty($filters['tenancy_agreement_expired_in'])) {
            $query->whereHas("latest_tenancy_agreement", function ($subQuery) use ($filters) {
                if (!empty($filters['is_tenancy_agreement_expired']) && filter_var($filters['is_tenancy_agreement_expired'], FILTER_VALIDATE_BOOLEAN)) {
                    $subQuery->whereDate('tenant_contact_expired_date', '<', \Carbon\Carbon::today());
                }
                if (!empty($filters['tenancy_agreement_expired_in'])) {
                    $expiryDays = $filters['tenancy_agreement_expired_in'];
                    if (is_numeric($expiryDays) && $expiryDays > 0) {
                        $subQuery->whereDate('tenant_contact_expired_date', '>', \Carbon\Carbon::today())
                                 ->whereDate('tenant_contact_expired_date', '<=', \Carbon\Carbon::today()->addDays((int) $expiryDays));
                    }
                }
            });
        }

        if (!empty($filters['category'])) {
            $query->where($tableName . ".category", $filters['category']);
        }

        if (!empty($filters['property_category'])) {
            $query->where($tableName . ".category", $filters['property_category']);
        }

        if (!empty($filters['property_type_id'])) {
            $query->where($tableName . ".property_type_id", $filters['property_type_id']);
        }

        if (!empty($filters['bed_id'])) {
            $query->where($tableName . ".bed_id", $filters['bed_id']);
        }

        if (!empty($filters['bath_id'])) {
            $query->where($tableName . ".bath_id", $filters['bath_id']);
        }

        if (!empty($filters['type'])) {
            $query->where($tableName . ".type", $filters['type']);
        }

        if (!empty($filters['address'])) {
            $query->where($tableName . ".address", "like", "%" . $filters['address'] . "%");
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate($tableName . '.created_at', ">=", $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate($tableName . '.created_at', "<=", $filters['end_date']);
        }
        if (!empty($filters['reference_no'])) {
            $query->where($tableName . ".reference_no", "like", "%" . $filters['reference_no'] . "%");
        }
        if (!empty($filters['start_date_of_instruction'])) {
            $query->whereDate($tableName . ".date_of_instruction", ">=", $filters['start_date_of_instruction']);
        }
        if (!empty($filters['end_date_of_instruction'])) {
            $query->whereDate($tableName . ".date_of_instruction", "<=", $filters['end_date_of_instruction']);
        }
        if (!empty($filters['no_of_beds'])) {
            $query->where($tableName . ".no_of_beds", ">=", $filters['no_of_beds']);
        }
        if (!empty($filters['start_no_of_beds'])) {
            $query->where($tableName . ".no_of_beds", ">=", $filters['start_no_of_beds']);
        }
        if (!empty($filters['end_no_of_beds'])) {
            $query->where($tableName . ".no_of_beds", "<=", $filters['end_no_of_beds']);
        }
        if (isset($filters['is_garden']) && $filters['is_garden'] !== '') {
            $query->where($tableName . ".is_garden", filter_var($filters['is_garden'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['is_dss']) && $filters['is_dss'] !== '') {
            $query->where($tableName . ".is_dss", filter_var($filters['is_dss'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['is_document_expired']) || !empty($filters['document_expired_in']) || !empty($filters['document_type_ids']) || !empty($filters['document_type_id']) || !empty($filters['start_document_end_date']) || !empty($filters['end_document_end_date'])) {
            $query->whereHas("latest_documents", function ($subQuery) use ($filters) {
                if (!empty($filters['is_document_expired']) && filter_var($filters['is_document_expired'], FILTER_VALIDATE_BOOLEAN)) {
                    $subQuery->whereDate('property_documents.gas_end_date', '<', \Carbon\Carbon::today());
                }
                if (!empty($filters['start_document_end_date'])) {
                    $subQuery->whereDate('property_documents.gas_end_date', '>=', $filters['start_document_end_date']);
                }
                if (!empty($filters['end_document_end_date'])) {
                    $subQuery->whereDate('property_documents.gas_end_date', '<=', $filters['end_document_end_date']);
                }
                if (!empty($filters['document_expired_in'])) {
                    $expiryDays = $filters['document_expired_in'];
                    if (is_numeric($expiryDays) && $expiryDays > 0) {
                        $subQuery->whereDate('property_documents.gas_end_date', '>', \Carbon\Carbon::today())
                                 ->whereDate('property_documents.gas_end_date', '<=', \Carbon\Carbon::today()->addDays((int) $expiryDays));
                    }
                }
                if (!empty($filters['document_type_ids'])) {
                    $document_type_ids = explode(',', $filters['document_type_ids']);
                    $subQuery->whereIn("property_documents.document_type_id", $document_type_ids);
                }
                if (!empty($filters['document_type_id'])) {
                    $subQuery->where('property_documents.document_type_id', $filters['document_type_id']);
                }
            });
        }

        if (!empty($filters['start_tenancy_agreement_date']) || !empty($filters['end_tenancy_agreement_date'])) {
            $query->whereHas("tenancy_agreements", function ($subQuery) use ($filters) {
                if (!empty($filters['start_tenancy_agreement_date'])) {
                    $subQuery->whereDate('tenancy_agreements.date_of_moving', '>=', $filters['start_tenancy_agreement_date']);
                }
                if (!empty($filters['end_tenancy_agreement_date'])) {
                    $subQuery->whereDate('tenancy_agreements.tenant_contact_expired_date', '<=', $filters['end_tenancy_agreement_date']);
                }
            });
        }

        if (!empty($filters['is_next_follow_up_date_passed']) && filter_var($filters['is_next_follow_up_date_passed'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($filters) {
                $subQuery->where("maintenance_items.status", "work_required")
                         ->whereDate('maintenance_items.next_follow_up_date', '<', \Carbon\Carbon::today());
                if (!empty($filters['maintenance_item_type_id'])) {
                    $subQuery->where('maintenance_items.maintenance_item_type_id', $filters['maintenance_item_type_id']);
                }
            });
        }

        if (!empty($filters['next_follow_up_date_in'])) {
            $expiryDays = $filters['next_follow_up_date_in'];
            if (is_numeric($expiryDays) && $expiryDays > 0) {
                $query->whereHas('latest_inspection.maintenance_item', function ($subQuery) use ($expiryDays, $filters) {
                    $subQuery->whereDate('maintenance_items.next_follow_up_date', '>', \Carbon\Carbon::today())
                             ->whereDate('maintenance_items.next_follow_up_date', '<=', \Carbon\Carbon::today()->addDays((int) $expiryDays));
                    if (!empty($filters['maintenance_item_type_id'])) {
                        $subQuery->where('maintenance_items.maintenance_item_type_id', $filters['maintenance_item_type_id']);
                    }
                });
            }
        }

        if (!empty($filters['is_next_inspection_date_passed']) && filter_var($filters['is_next_inspection_date_passed'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereHas("latest_inspection", function ($subQuery) {
                $subQuery->whereDate('tenant_inspections.next_inspection_date', '<', \Carbon\Carbon::today());
            });
        }

        if (!empty($filters['next_inspection_date_in'])) {
            $expiryDays = $filters['next_inspection_date_in'];
            if (is_numeric($expiryDays) && $expiryDays > 0) {
                $query->whereHas('latest_inspection', function ($subQuery) use ($expiryDays) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', '>', \Carbon\Carbon::today())
                             ->whereDate('tenant_inspections.next_inspection_date', '<=', \Carbon\Carbon::today()->addDays((int) $expiryDays));
                });
            }
        }

        if (!empty($filters['start_inspection_date']) || !empty($filters['end_inspection_date'])) {
            $query->whereHas('latest_inspection', function ($subQuery) use ($filters) {
                if (!empty($filters['start_inspection_date'])) {
                    $subQuery->whereDate('tenant_inspections.date', '>=', $filters['start_inspection_date']);
                }
                if (!empty($filters['end_inspection_date'])) {
                    $subQuery->whereDate('tenant_inspections.date', '<=', $filters['end_inspection_date']);
                }
            });
        }

        if (!empty($filters['start_next_inspection_date']) || !empty($filters['end_next_inspection_date'])) {
            $query->whereHas('latest_inspection', function ($subQuery) use ($filters) {
                if (!empty($filters['start_next_inspection_date'])) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', '>=', $filters['start_next_inspection_date']);
                }
                if (!empty($filters['end_next_inspection_date'])) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', '<=', $filters['end_next_inspection_date']);
                }
            });
        }

        if (!empty($filters['inspected_by'])) {
            $query->whereHas('latest_inspection', function ($subQuery) use ($filters) {
                $subQuery->where('tenant_inspections.inspected_by', 'like', '%' . $filters['inspected_by'] . '%');
            });
        }

        if (!empty($filters['inspection_duration'])) {
            $query->whereHas('latest_inspection', function ($subQuery) use ($filters) {
                $subQuery->where('tenant_inspections.inspection_duration', 'like', '%' . $filters['inspection_duration'] . '%');
            });
        }

        if (!empty($filters['maintenance_item_type_id'])) {
            $query->whereHas('latest_inspection.maintenance_item', function ($subQuery) use ($filters) {
                $subQuery->where('maintenance_items.maintenance_item_type_id', $filters['maintenance_item_type_id']);
            });
        }

        if (!empty($filters['start_next_follow_up_date']) || !empty($filters['end_next_follow_up_date'])) {
            $query->whereHas('latest_inspection.maintenance_item', function ($subQuery) use ($filters) {
                if (!empty($filters['start_next_follow_up_date'])) {
                    $subQuery->whereDate('maintenance_items.next_follow_up_date', '>=', $filters['start_next_follow_up_date']);
                }
                if (!empty($filters['end_next_follow_up_date'])) {
                    $subQuery->whereDate('maintenance_items.next_follow_up_date', '<=', $filters['end_next_follow_up_date']);
                }
            });
        }

        $query->groupBy($tableName . ".id")
              ->select(
                  $tableName . ".*",
                  \Illuminate\Support\Facades\DB::raw('
                      COALESCE(
                          (SELECT COUNT(invoices.id) FROM invoices WHERE invoices.property_id = ' . $tableName . '.id),
                          0
                      ) AS total_invoice
                  ')
              );

        if (!empty($filters['order_by'])) {
            $query->orderBy($tableName . ".address", $filters['order_by']);
        }

        return $query;
    }

}
