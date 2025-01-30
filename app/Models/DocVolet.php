<?php



namespace App\Models;

use App\Http\Utils\DefaultQueryScopesTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocVolet extends Model
{
    use HasFactory, DefaultQueryScopesTrait;
    protected $fillable = [
                    'title',
                    'description',
                    'date',
                    'note',
                    'files',
                    'property_id',
      
        "created_by"
    ];

    protected $casts = [
      'files' => 'array',          ];




    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id','id');
    }




}

