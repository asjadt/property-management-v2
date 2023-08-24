<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillSaleItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'item',
        'description',
        'amount',
        'sale_id',
        "bill_id",
        ];
}
