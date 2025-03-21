<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accreditation extends Model
{
    use HasFactory, DefaultQueryScopesTrait;

    protected $fillable = [
                    'name',
                    'description',
                    'accreditation_start_date',
                    'accreditation_expiry_date',
                    'logo',

       "is_active",
        "created_by"
    ];

    protected $casts = [
                 ];










}

