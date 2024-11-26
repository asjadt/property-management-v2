<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'gas_start_date',
        'gas_end_date',
        'description',
        'document_type_id',
        'property_id',
        'files' // Added files field
    ];

    protected $casts = [
        'files' => 'array', // Cast 'files' as array
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }


}
