<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'status',
        'from_date',
        'to_date',
        'note',
        'changed_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
}
