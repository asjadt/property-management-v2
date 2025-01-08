<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'address_line_1',
        'inspected_by',
        'phone',
        'date',
        'next_inspection_date',
        'comments',
        'files',
        'created_by'
    ];

    protected $casts = [
        'files' => 'array',
    ];

    public function property()
    {
        return $this->belongsTo(Tenant::class,"tenant_id","id");
    }


    public function tenant()
    {
        return $this->belongsTo(Tenant::class,"tenant_id","id");
    }


    public function maintenance_item()
    {
        return $this->hasMany(MaintenanceItem::class,"tenant_inspection_id","id");
    }











}
