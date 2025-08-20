<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
   use HasFactory,SoftDeletes;
     protected $appends = ['linked_to'];
    protected $fillable = [
        "payment_method",
        'property_id',
        'expense_category_id',
        "paid_by",
        'item_description',
        'status',
        'receipt',
        'price',
        'create_date',
        "created_by"

    ];

   

    public function invoice_items()
    {
        return $this->hasMany(InvoiceItem::class, 'expense_id');
    }

    public function rent_adjustments()
    {
        return $this->hasMany(RentAdjustment::class, 'expense_id');
    }

    public function getLinkedToAttribute()
{
    // Check if there is any linked invoice item
    $invoice_item = $this->invoice_items()->first();
    if ($invoice_item) {
        return [
            'type' => 'invoice',
            'entity_id' => $invoice_item->invoice_id
        ];
    }

    // Check if there is any linked rent adjustment
    $rent_adjustment = $this->rent_adjustments()->first();
    if ($rent_adjustment) {
        return [
            'type' => 'landlord_payable',
            'entity_id' => $rent_adjustment->landlord_rent_payable_id
        ];
    }
    return null; // unlinked
}
    public function expense_category() {
        return $this->hasOne(ExpenseCategory::class,'id','expense_category_id');
    }

    public function property() {
        return $this->hasOne(Property::class,'id','property_id');
    }
 
    protected $casts = [
        'receipt' => 'array',
    ];
}
