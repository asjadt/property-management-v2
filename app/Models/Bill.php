<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'create_date',
        'property_id',
        'landlord_id',
        'payment_mode',
        "payabble_amount",
        "remarks",
        "created_by"
        ];

        public function bill_bill_items(){
            return $this->hasMany(BillBillItem::class,'bill_id', 'id');
        }

        public function bill_sale_items(){
            return $this->hasMany(BillSaleItem::class,'bill_id', 'id');
        }

        public function bill_repair_items(){
            return $this->hasMany(BillRepairItem::class,'bill_id', 'id');
        }



}
