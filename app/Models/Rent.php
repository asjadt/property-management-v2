<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    use HasFactory, DefaultQueryScopesTrait;

    protected $fillable = [
                    'tenancy_agreement_id',
                    'payment_date',
                    'payment_status',
                    'rent_amount',
                    'paid_amount',
                    'month',
                    'year',
        "created_by"
    ];

    protected $casts = [
                  ];



    public function tenancy_agreement()
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id','id');
    }








}

