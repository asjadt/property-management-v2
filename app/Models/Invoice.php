<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{








    use HasFactory;
    protected $fillable = [

        "logo",
        "invoice_title",
        "invoice_summary",
        "business_name",
        "business_address",
        "invoice_payment_due",
        "invoice_date",
        "footer_text",
        "property_id",
        "landlord_id",
        "tenant_id",


    ];

    public function invoice_items(){
        return $this->hasMany(InvoiceItem::class,'invoice_id', 'id');
    }

    public function tenant(){
        return $this->belongsTo(Tenant::class,'tenant_id', 'id');
    }
    public function landlord(){
        return $this->belongsTo(Tenant::class,'landlord_id', 'id');
    }
    public function property(){
        return $this->belongsTo(Tenant::class,'property_id', 'id');
    }

}
