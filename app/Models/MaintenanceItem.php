<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_inspection_id',
        'item',
        'status',
        'comment',
        'next_follow_up_date',
    ];



}
