<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
   use HasFactory,SoftDeletes;
    protected $fillable = [
        "payment_method",
        'property_id',
        'expense_category_id',
        'item_description',
        'status',
        'receipt',
        'price',
        'create_date',
        "created_by"

    ];

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
