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

    protected $casts = [];


    // RENT RELATION WITH TENANCY AGREEMENT
    public function tenancy_agreement()
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id', 'id');
    }

    // AUTO GENERATE RENT REFERENCE NO

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
