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
                    'min_price',
                    'max_price',
                    'address_line_1',
                    'latitude',
                    'longitude',
                    'radius',
                    'property_type',
                    'no_of_beds',
                    'no_of_baths',
                    'deadline_to_move',
                    'working',
                    'job_title',
                    'is_dss',

                  "is_active",



        "business_id",
        "created_by"
    ];

    protected $casts = [
  ];
































}

