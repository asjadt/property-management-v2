<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory, DefaultQueryScopesTrait;

    protected $fillable = [
        "rent_reference",
        "payment_method",
        'rent_taken_by',
        'remarks',
        'tenancy_agreement_id',
        'payment_date',
        'payment_status',
        'rent_amount',
        'paid_amount',
        'arrear',
        'month',
        'year',
        "created_by"
    ];

    // RENT RELATION WITH TENANCY AGREEMENT
    public function tenancy_agreement()
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id', 'id');
    }

    public function landlord_payables()
    {
        return $this->hasMany(LandlordPayableRent::class, 'rent_id', 'id');
    }

    public function getLandlordsAttribute()
    {
        return $this->tenancy_agreement->property->property_landlords ?? collect();
    }
    // AUTO GENERATE RENT REFERENCE NO

    public function scopeFilters($query)
    {

        return $query->where('rents.created_by', auth()->user()->id)
            ->when(request()->filled("tenant_ids"), function ($query) {
                return $query->whereHas("tenancy_agreement.tenants", function ($query) {
                    $tenant_ids = explode(',', request()->input("tenant_ids"));
                    $query->whereIn("tenants.id", $tenant_ids);
                });
            })
            ->when(request()->filled("landlord_ids"), function ($query) {
                return $query->whereHas("tenancy_agreement.property.property_landlords", function ($query) {
                    $landlord_ids = explode(',', request()->input("landlord_ids"));
                    $query->whereIn("landlords.id", $landlord_ids);
                });
            })
            ->when(request()->filled("property_ids"), function ($query) {
                return $query->whereHas("tenancy_agreement", function ($query) {
                    $property_ids = explode(',', request()->input("property_ids"));
                    $query->whereIn("tenancy_agreements.property_id", $property_ids);
                });
            })
            // ->when(request()->filled("invoice_not_issued"), function ($query) {
            //     $query->whereDoesntHave("landlord_payables");
            // })
            ->when(request()->filled("rent_reference"), function ($query) {
                return $query->where('rents.rent_reference', "like", "%" .  request()->input("rent_reference") . "%");
            })

            ->when(request()->filled("start_payment_date"), function ($query) {
                return $query->whereDate(
                    'rents.payment_date',
                    ">=",
                    request()->input("start_payment_date")
                );
            })

            ->when(request()->filled("end_payment_date"), function ($query) {
                return $query->whereDate('rents.payment_date', "<=", request()->input("end_payment_date"));
            })
            ->when(request()->filled("payment_status"), function ($query) {
                return $query->where(
                    'rents.payment_status',
                    request()->input("payment_status")
                );
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("rents.payment_status", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('rents.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('rents.created_at', "<=", request()->input("end_date"));
            })
            ->orderBy('rents.tenancy_agreement_id')
            ->orderBy('rents.year');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->rent_reference)) {
                $userId = $model->created_by ?? auth()->id();
                $current_number = 1;

                do {
                    $rent_reference = str_pad($current_number, 4, '0', STR_PAD_LEFT);
                    $current_number++;
                } while (
                    self::where([
                        'rent_reference' => $rent_reference,
                        'created_by' => $userId
                    ])->exists()
                );

                $model->rent_reference = $rent_reference;
            }
        });
    }
}
