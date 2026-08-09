<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'create_date',
        "payment_date",
        'property_id',
        'payment_mode',
        "payabble_amount",
        "deduction",
        "remarks",
        "created_by"
        ];

        public function bill_bill_items(){
            return $this->hasMany(BillBillItem::class,'bill_id', 'id');
        }

        public function bill_sale_items(){
            return $this->hasMany(BillSaleItem::class,'bill_id', 'id');
        }

        public function bill_repair_items(){
            return $this->hasMany(BillRepairItem::class,'bill_id', 'id');
        }


        public function landlords() {
            return $this->belongsToMany(Landlord::class, 'bill_landlords', 'bill_id', 'landlord_id');
        }

        public function property(){
            return $this->belongsTo(Property::class,'property_id', 'id');
        }


        public function scopeBillFilters($query, array $filters = [])
        {
            $tableName = $this->getTable();

            $query->with("bill_bill_items", "bill_sale_items", "bill_repair_items", "landlords", "property")
                ->leftJoin('invoices', 'invoices.bill_id', '=', $tableName . '.id');

            if (auth()->check()) {
                $query->where($tableName . ".created_by", auth()->id());
            }

            if (!empty($filters['landlord_ids']) || !empty($filters['landlord_id'])) {
                $query->whereHas("landlords", function ($q) use ($filters) {
                    $landlord_ids = !empty($filters['landlord_ids']) ? explode(',', $filters['landlord_ids']) : explode(',', $filters['landlord_id']);
                    $q->whereIn("landlords.id", $landlord_ids);
                });
            }

            if (!empty($filters['tenant_ids']) || !empty($filters['tenant_id'])) {
                $query->whereHas("tenants", function ($q) use ($filters) {
                    $tenant_ids = !empty($filters['tenant_ids']) ? explode(',', $filters['tenant_ids']) : explode(',', $filters['tenant_id']);
                    $q->whereIn("tenants.id", $tenant_ids);
                });
            }

            if (!empty($filters['start_date'])) {
                $query->whereDate($tableName . '.create_date', ">=", $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->whereDate($tableName . '.create_date', "<=", $filters['end_date']);
            }

            if (!empty($filters['min_amount'])) {
                $query->where($tableName . '.payabble_amount', ">=", $filters['min_amount']);
            }

            if (!empty($filters['max_amount'])) {
                $query->where($tableName . '.payabble_amount', "<=", $filters['max_amount']);
            }

            if (!empty($filters['search_key'])) {
                $query->where(function ($q) use ($filters) {
                    $term = $filters['search_key'];
                    $q->whereHas('bill_bill_items', function ($q2) use ($term) {
                        $q2->where('item', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('bill_sale_items', function ($q2) use ($term) {
                        $q2->where('item', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('bill_repair_items', function ($q2) use ($term) {
                        $q2->where('item', 'like', '%' . $term . '%');
                    });
                });
            }

            if (!empty($filters['status'])) {
                if ($filters['status'] == "unpaid") {
                    $query->whereNotIn("invoices.status", ['draft', 'paid']);
                } else if ($filters['status'] == "next_15_days_invoice_due") {
                    $currentDate = \Carbon\Carbon::now();
                    $endDate = $currentDate->copy()->addDays(15);
                    $query->whereNotIn("invoices.status", ['draft', 'paid'])
                          ->whereDate('invoices.due_date', '>=', $currentDate)
                          ->whereDate('invoices.due_date', '<=', $endDate);
                } else {
                    $query->where("status", $filters['status']);
                }
            }

            if (!empty($filters['invoice_reference'])) {
                $query->where("invoices.invoice_reference", "like", "%" . $filters['invoice_reference'] . "%");
            }

            if (!empty($filters['client_id'])) {
                $query->where("invoices.client_id", $filters['client_id']);
            }

            if (!empty($filters['property_id'])) {
                $query->where($tableName . ".property_id", $filters['property_id']);
            }

            if (!empty($filters['property_ids'])) {
                $property_ids = array_filter(is_array($filters['property_ids']) ? $filters['property_ids'] : explode(',', $filters['property_ids']));
                if (count($property_ids)) {
                    $query->whereIn($tableName . ".property_id", array_values($property_ids));
                }
            }

            $query->groupBy($tableName . ".id")
                ->select(
                    $tableName . ".*",
                    "invoices.id as invoice_id",
                    "invoices.generated_id as invoice_generated_id",
                    "invoices.invoice_reference"
                );

            if (!empty($filters['order_by'])) {
                $query->orderBy($tableName . ".id", $filters['order_by']);
            }

            return $query;
        }

}
