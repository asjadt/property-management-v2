<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Repair extends Model
{
    use HasFactory,SoftDeletes;

    protected $appends = ['linked_to','note'];
    protected $fillable = [
        'property_id',
        'repair_category_id',
        "paid_by",
        'item_description',
        'status',
        'receipt',
        'price',
        'create_date',
        "created_by",

    ];

    public function invoice_items()
    {
        return $this->hasMany(InvoiceItem::class, 'repair_id');
    }

    public function rent_adjustments()
    {
        return $this->hasMany(RentAdjustment::class, 'repair_id');
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

   public function getNoteAttribute()
    {
        $linked = $this->linked_to;

        if (!$linked) {
            return null;
        }

        if ($linked['type'] === 'invoice') {
            $invoice = Invoice::find($linked['entity_id']);
            $payment = $invoice?->invoice_payments()->first();
            if ($payment) {
                $date = \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y');
                return "Paid by Landlord, adjusted against Invoice Payment ID {$payment->id} dated {$date}";
            } elseif ($invoice) {
                return "Linked to Invoice ID {$invoice->id} but no payment yet";
            }
        }

        if ($linked['type'] === 'landlord_payable') {
            $rent_adjustment = RentAdjustment::where('landlord_rent_payable_id', $linked['entity_id'])
                                ->where('repair_id', $this->id)
                                ->first();
            $landlord_payment = $rent_adjustment?->landlord_rent_payable;
            if ($landlord_payment) {
                $date = \Carbon\Carbon::parse($landlord_payment->create_date)->format('d-m-Y');
                return "Paid by landlord, adjusted against Landlord Payment ID {$landlord_payment->id} dated {$date}";
            }
        }

        return null;
    }

    public function repair_category() {
        return $this->hasOne(RepairCategory::class,'id','repair_category_id');
    }

    public function property() {
        return $this->hasOne(Property::class,'id','property_id');
    }
    public function repair_images() {
        return $this->hasMany(RepairImage::class,'repair_id','id');
    }
    protected $casts = [
        'receipt' => 'array',
    ];
}
