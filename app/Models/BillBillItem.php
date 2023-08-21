<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'description',
        'amount',
        'bill_item_id',
        "bill_id",
        ];
}
