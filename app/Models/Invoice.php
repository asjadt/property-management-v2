<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{

    use HasFactory,SoftDeletes;
    protected $fillable = [
        "logo",
        "invoice_title",
        "invoice_summary",
        "invoice_number",
        "business_name",
        "business_address",
        "sub_total",
        "total_amount",
        "invoice_date",
        "footer_text",
        "note",
        "property_id",
        "landlord_id",
        "tenant_id",
        "discount_description",
        "discound_type",
        "discount_amount",
        "due_date",
        "status",
        "created_by"
    ];

    public function invoice_items(){
        return $this->hasMany(InvoiceItem::class,'invoice_id', 'id');
    }
    public function invoice_payments(){
        return $this->hasMany(InvoicePayment::class,'invoice_id', 'id');
    }

    public function invoice_reminder(){
        return $this->hasMany(InvoiceReminder::class,'invoice_id', 'id');
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
