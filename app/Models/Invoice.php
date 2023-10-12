<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{

    use HasFactory;

    protected $fillable = [
        "logo",
        "invoice_title",
        "invoice_summary",
        "invoice_reference",
        "business_name",
        "business_address",
        "sub_total",
        "total_amount",
        "invoice_date",
        "footer_text",
        "shareable_link",
        "note",
        "property_id",
        "landlord_id",
        "tenant_id",
        "client_id",
        "discount_description",
        "discound_type",
        "discount_amount",
        "due_date",
        "status",
        "bill_id",
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
        return $this->belongsTo(Landlord::class,'landlord_id', 'id');
    }
    public function client(){
        return $this->belongsTo(Client::class,'client_id', 'id');
    }
    public function property(){
        return $this->belongsTo(Property::class,'property_id', 'id');
    }

}
