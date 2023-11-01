<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'description',
        "is_default",
        'price',
        'created_by'
        ];








}
