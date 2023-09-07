<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'tenant_id',
        "tenant_name",
        'property_address',
        'amount',
        'receipt_by',
        'receipt_date',
        "created_by",
        "notes",
        "payment_method"
    ];

    public function tenant() {
        return $this->belongsTo(Tenant::class,'tenant_id','id');
    }

    public function property_address() {
        return $this->belongsTo(Property::class,'property_address','address');
    }
    public function property() {
        return $this->belongsTo(Property::class,'property_address','address');
    }

    public function receipt_sale_items(){
        return $this->hasMany(ReceiptSaleItem::class,'receipt_id', 'id');
    }
}
