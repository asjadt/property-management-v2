<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $hidden = [ "invoice_id",
    "repair_id"];
    protected $fillable = [

        "name",
        "description",
        "quantity",
        "price",
        "tax",
        "amount",
        "invoice_id",
        "repair_id"

    ];
}
