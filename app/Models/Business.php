<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "about",
        "web_page",
        "phone",
        "email",
        "additional_information",
        "address_line_1",
        "address_line_2",
        "lat",
        "long",
        "country",
        "city",
        "currency",
        "postcode",
        "logo",
        "image",
        "status",
        "owner_id",
        "created_by",
        "invoice_title",
        "footer_text",
       "is_reference_manual",
       "receipt_footer",
       "account_name" ,
       "account_number",
       "send_email_alert",
       "sort_code",
       "pin" ,
       "type" ,
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id', 'id');
    }

    public function reminder(){
        return $this->hasMany(Reminder::class,'created_by', 'owner_id');
    }

}
