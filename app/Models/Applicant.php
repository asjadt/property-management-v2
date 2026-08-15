<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'customer_name',
                    'customer_phone',
                    'email',
                    'min_price',
                    'max_price',
                    'address_line_1',
                    "country",
                    "city",
                    "postcode",
                    'latitude',
                    'longitude',
                    'radius',
                    'property_type',
                    'no_of_beds',
                    'no_of_baths',
                    'deadline_to_move',
                    'expiry_date',
                    'working',
                    'job_title',
                    'is_dss',
                    "is_active",
                    "is_send_alert",
                    "created_by",
                    "tenant_id"

    ];

    protected $casts = [
  ];



    public function property_types()
    {
        return $this->belongsToMany(PropertyType::class, 'applicant_property_type');
    }

    public function beds()
    {
        return $this->belongsToMany(Bed::class, 'applicant_bed');
    }

    public function baths()
    {
        return $this->belongsToMany(Bath::class, 'applicant_bath');
    }

    public function scopeApplicantFilters($query, array $filters = [])
    {
        if (auth()->check()) {
            $query->where($this->getTable() . '.created_by', auth()->id());
        }
        
        $query->whereNull($this->getTable() . ".tenant_id")
            ->when(!empty($filters['customer_name']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.customer_name', $filters['customer_name']);
            })
            ->when(!empty($filters['customer_phone']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.customer_phone', $filters['customer_phone']);
            })
            ->when(!empty($filters['email']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.email', $filters['email']);
            })
            ->when(!empty($filters['address_line_1']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.address_line_1', $filters['address_line_1']);
            })
            ->when(!empty($filters['property_type_ids']), function ($query) use ($filters) {
                $ids = is_array($filters['property_type_ids']) ? $filters['property_type_ids'] : explode(',', $filters['property_type_ids']);
                return $query->whereHas('property_types', function($q) use ($ids) {
                    $q->whereIn('property_types.id', $ids);
                });
            })
            ->when(!empty($filters['bed_ids']), function ($query) use ($filters) {
                $ids = is_array($filters['bed_ids']) ? $filters['bed_ids'] : explode(',', $filters['bed_ids']);
                return $query->whereHas('beds', function($q) use ($ids) {
                    $q->whereIn('beds.id', $ids);
                });
            })
            ->when(!empty($filters['bath_ids']), function ($query) use ($filters) {
                $ids = is_array($filters['bath_ids']) ? $filters['bath_ids'] : explode(',', $filters['bath_ids']);
                return $query->whereHas('baths', function($q) use ($ids) {
                    $q->whereIn('baths.id', $ids);
                });
            })
            ->when(!empty($filters['property_type']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.property_type', $filters['property_type']);
            })
            ->when(!empty($filters['no_of_beds']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.no_of_beds', $filters['no_of_beds']);
            })
            ->when(!empty($filters['no_of_baths']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.no_of_baths', $filters['no_of_baths']);
            })
            ->when(!empty($filters['start_deadline_to_move']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.deadline_to_move', ">=", $filters['start_deadline_to_move']);
            })
            ->when(!empty($filters['end_deadline_to_move']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.deadline_to_move', "<=", ($filters['end_deadline_to_move'] . ' 23:59:59'));
            })
            ->when(!empty($filters['working']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.working', $filters['working']);
            })
            ->when(!empty($filters['job_title']), function ($query) use ($filters) {
                return $query->where($this->getTable() . '.job_title', $filters['job_title']);
            })
            ->when(!empty($filters['search_key']), function ($query) use ($filters) {
                return $query->where(function ($q) use ($filters) {
                    $term = $filters['search_key'];
                    $q->orWhere($this->getTable() . ".customer_name", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".customer_phone", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".address_line_1", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".property_type", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".no_of_beds", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".no_of_baths", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".working", "like", "%" . $term . "%")
                      ->orWhere($this->getTable() . ".job_title", "like", "%" . $term . "%");
                });
            })
            ->when(!empty($filters['start_date']), function ($query) use ($filters) {
                return $query->whereDate($this->getTable() . '.created_at', ">=", $filters['start_date']);
            })
            ->when(!empty($filters['end_date']), function ($query) use ($filters) {
                return $query->whereDate($this->getTable() . '.created_at', "<=", ($filters['end_date']));
            });

        return $query;
    }
}

