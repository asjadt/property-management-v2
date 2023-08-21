<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillRepairItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'item',
        'description',
        'amount',
        'repair_id',
        "bill_id",
        ];
}
