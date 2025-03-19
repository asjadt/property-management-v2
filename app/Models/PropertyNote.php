<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyNote extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'title',
                    'description',
                    'property_id',

      
        "created_by"
    ];

    protected $casts = [
             ];



    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id','id');
    }
















}

