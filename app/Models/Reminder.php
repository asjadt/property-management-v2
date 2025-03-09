<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'duration',
        'duration_unit',
        'send_time',
        'frequency_after_first_reminder',
        'reminder_limit',
        'keep_sending_until_update',
        'entity_name',
        "property_id",
        "created_by"
    ];

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }



}
