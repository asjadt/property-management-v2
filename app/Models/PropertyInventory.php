<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyInventory extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'item_name',
                    'item_location',
                    'item_quantity',
                    'item_condition',
                    'item_details',
                    'property_id',
                    'files',
        "created_by",
    ];

    protected $casts = [
        'files' => 'array',
    ];


    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id','id');
    }




}

