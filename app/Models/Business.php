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
        "footer_text",
       "is_reference_manual",
       "account_name" ,
       "account_number",
       "sort_code",
       "pin" ,
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id', 'id');
    }

}
