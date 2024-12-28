<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'address_line_1',
        'inspected_by',
        'phone',
        'date',
        'garden',
        'entrance',
        'exterior_paintwork',
        'windows_outside',
        'kitchen_floor',
        'oven',
        'sink',
        'lounge',
        'downstairs_carpet',
        'upstairs_carpet',
        'window_1',
        'window_2',
        'window_3',
        'window_4',
        'windows_inside',
        'wc',
        'shower',
        'bath',
        'hand_basin',
        'smoke_detective',
        'general_paintwork',
        'carbon_monoxide',
        'overall_cleanliness',
        'comments',
        'created_by'
    ];




    public function tenant()
    {
        return $this->belongsTo(Tenant::class,"tenant_id","id");
    }






}
