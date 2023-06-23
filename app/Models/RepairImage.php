<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'repair_id',
        'image',
    ];
}
