<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'description',
        'amount',
        'repair_id',
        "receipt_id",
        ];
}
