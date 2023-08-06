<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;
    protected $fillable = [
        "amount",
        "payment_method",
        "payment_date",
        "note",
        "invoice_id",
        "shareable_link"
    ];


}
