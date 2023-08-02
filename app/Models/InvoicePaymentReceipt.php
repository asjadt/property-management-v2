<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePaymentReceipt extends Model
{
    use HasFactory;
    protected $fillable =  [
        "invoice_id",
        "invoice_payment_id",
        "from",
        "to",
        "subject",
        "message" ,
        "copy_to_myself",
        "shareable_link"
    ];


    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id', 'id');
    }

    public function invoice_payment(){
        return $this->belongsTo(InvoicePayment::class,'invoice_payment_id', 'id');
    }

}
