<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceReminder extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        "reminder_date_amount",
        "reminder_status",
        "send_reminder",
        "reminder_date",
        "invoice_id"
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id', 'id');
    }
}
